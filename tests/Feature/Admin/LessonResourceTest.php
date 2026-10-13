<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_private_lesson_resource(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $module = $this->module();

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'SOP Penjualan',
            'type' => 'pdf',
            'is_required' => true,
            'sequence' => 1,
            'resource' => UploadedFile::fake()->create('sop-penjualan.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

        $resource = \DB::table('lesson_resources')->first();

        $this->assertNotNull($resource);
        Storage::disk('local')->assertExists($resource->path);
    }

    public function test_employee_cannot_download_private_lesson_resource(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'published']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.resources.download', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertForbidden();
    }

    public function test_enrolled_employee_can_preview_a_private_pdf(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'published']);
        $employee = User::factory()->create();
        $employee->enrollments()->create(['course_id' => $module->course_id, 'status' => 'active']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs($employee)
            ->get(route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertOk();
    }

    public function test_enrolled_employee_cannot_access_an_archived_course_resource(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'archived']);
        $employee = User::factory()->create();
        $employee->enrollments()->create(['course_id' => $module->course_id, 'status' => 'active']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs($employee)
            ->get(route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertNotFound();
    }

    private function module(): CourseModule
    {
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'passing_grade' => 70]);

        return $course->modules()->create(['title' => 'Mulai di KGJ', 'sequence' => 1]);
    }

    private function courseAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-courses', 'name' => 'Kelola course']);
        $role = Role::query()->create(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
