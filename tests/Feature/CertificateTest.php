<?php

namespace Tests\Feature;

use App\Jobs\IssueCertificate;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_certificate_course_issues_one_private_pdf(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create([
            'category_id' => $category->id,
            'code' => 'CERT-001',
            'title' => 'Standar Layanan KGJ',
            'passing_grade' => 70,
            'status' => 'published',
            'certificate_enabled' => true,
        ]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id, 'status' => 'completed', 'completed_at' => now()]);

        (new IssueCertificate($enrollment->id))->handle();
        (new IssueCertificate($enrollment->id))->handle();

        $certificate = Certificate::query()->sole();
        $this->assertSame($enrollment->id, $certificate->enrollment_id);
        $this->assertNotNull($certificate->issued_at);
        Storage::disk('local')->assertExists($certificate->path);
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_only_owner_or_lms_admin_can_download_a_certificate(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $lmsAdmin = User::factory()->create();
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment belajar']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin / HR']);
        $role->permissions()->attach($permission);
        $lmsAdmin->roles()->attach($role);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'CERT-002', 'title' => 'Produk KGJ', 'passing_grade' => 70, 'status' => 'published']);
        $enrollment = $owner->enrollments()->create(['course_id' => $course->id, 'status' => 'completed', 'completed_at' => now()]);
        Storage::disk('local')->put('certificates/example.pdf', 'pdf');
        $certificate = Certificate::query()->create(['enrollment_id' => $enrollment->id, 'user_id' => $owner->id, 'course_id' => $course->id, 'number' => 'KGJ-2026-000001', 'path' => 'certificates/example.pdf', 'issued_at' => now()]);

        $this->actingAs($otherUser)->get(route('certificates.download', $certificate))->assertForbidden();
        $this->actingAs($owner)->get(route('certificates.download', $certificate))->assertOk();
        $this->actingAs($lmsAdmin)->get(route('certificates.index'))->assertOk();
        $this->actingAs($lmsAdmin)->get(route('certificates.download', $certificate))->assertOk();
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $owner->id, 'event' => 'certificate.downloaded']);
    }
}
