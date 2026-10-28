<?php

namespace Tests\Feature;

use App\Jobs\GenerateReportExport;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\Permission;
use App\Models\ReportExport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_is_paginated_and_limited_to_authorized_users(): void
    {
        $admin = $this->reportAdmin();
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales', 'is_active' => true]);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operasional', 'is_active' => true]);
        $employee = User::factory()->create(['name' => 'Ayu Wijaya', 'employee_id' => 'KGJ-001', 'division_id' => $sales->id]);
        $otherEmployee = User::factory()->create(['name' => 'Bima Aditya', 'employee_id' => 'KGJ-002', 'division_id' => $operations->id]);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $otherEmployee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs(User::factory()->create())->get(route('reports.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('reports.index', ['search' => 'Ayu', 'division_id' => $sales->id]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('reports/Index')
            ->has('enrollments.data', 1)
            ->where('enrollments.data.0.user.name', 'Ayu Wijaya')
            ->where('filters.search', 'Ayu')
            ->where('filters.division_id', $sales->id));
    }

    public function test_authorized_user_can_queue_and_download_a_private_csv_export(): void
    {
        Queue::fake();
        $admin = $this->reportAdmin();
        $division = Division::query()->create(['code' => 'SLS', 'name' => 'Sales', 'is_active' => true]);
        $employee = User::factory()->create(['employee_id' => 'KGJ-001', 'division_id' => $division->id]);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($admin)->post(route('reports.exports.store'), ['status' => 'active', 'division_id' => $division->id])->assertRedirect(route('reports.index', ['search' => '', 'status' => 'active', 'division_id' => $division->id]));
        $export = ReportExport::query()->firstOrFail();
        $this->assertSame('queued', $export->status);
        Queue::assertPushed(GenerateReportExport::class, fn (GenerateReportExport $job) => $job->reportExportId === $export->id);

        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        (new GenerateReportExport($export->id))->handle();
        $export->refresh();
        $this->assertSame('ready', $export->status);
        Storage::disk('s3')->assertExists($export->path);
        $this->assertStringContainsString('KGJ-001', Storage::disk('s3')->get($export->path));
        $this->actingAs($admin)->get(route('reports.exports.download', $export))->assertOk();
    }

    public function test_course_search_is_authorized_and_finds_courses_beyond_the_previous_limit(): void
    {
        $admin = $this->reportAdmin();
        $employee = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);

        foreach (range(1, 101) as $number) {
            $course = Course::query()->create([
                'category_id' => $category->id,
                'code' => sprintf('ONB-%03d', $number),
                'title' => sprintf('Course %03d', $number),
                'status' => 'published',
            ]);
            $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        }

        $this->actingAs(User::factory()->create())->getJson(route('reports.courses', ['q' => '101']))->assertForbidden();
        $this->actingAs($admin)->getJson(route('reports.courses', ['q' => '101']))
            ->assertOk()
            ->assertJsonPath('0.code', 'ONB-101');
    }

    public function test_export_neutralizes_csv_formula_values(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        $admin = $this->reportAdmin();
        $employee = User::factory()->create(['name' => '=HYPERLINK("https://example.test")', 'employee_id' => 'KGJ-004']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-004', 'title' => '+Formula', 'status' => 'published']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $export = $admin->reportExports()->create(['filters' => [], 'status' => 'queued']);

        (new GenerateReportExport($export->id))->handle();

        $contents = Storage::disk('s3')->get($export->fresh()->path);
        $this->assertStringContainsString("'=HYPERLINK", $contents);
        $this->assertStringContainsString("'+Formula", $contents);
    }

    public function test_report_shows_pre_test_baseline_best_post_test_and_improvement_for_not_passed_enrollments(): void
    {
        $admin = $this->reportAdmin();
        $employee = User::factory()->create(['name' => 'Ayu Wijaya', 'employee_id' => 'KGJ-003']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-003', 'title' => 'Evaluasi KGJ', 'status' => 'published']);
        $enrollment = $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'not_passed']);
        $preTest = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $postTest = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 80, 'attempt_limit' => 3]);
        $preTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 55, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 80, 'passed' => true, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 2, 'score' => 90, 'passed' => true, 'submitted_at' => now()]);

        $this->actingAs($admin)->get(route('reports.index', ['status' => 'not_passed']))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('enrollments.data.0.status', 'not_passed')
            ->where('enrollments.data.0.pre_test_score', 55)
            ->where('enrollments.data.0.best_post_score', 90)
            ->where('enrollments.data.0.improvement', 35)
            ->where('enrollments.data.0.post_test_attempts', 2));
    }

    public function test_report_marks_course_level_evaluation_as_unused_when_assessments_only_exist_per_module(): void
    {
        $admin = $this->reportAdmin();
        $employee = User::factory()->create(['name' => 'Ayu Wijaya']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-MODULE-001', 'title' => 'Evaluasi Modul', 'status' => 'published']);
        $enrollment = $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $module = $course->modules()->create(['title' => 'Dasar penjualan', 'sequence' => 1]);
        $preTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 1]);
        $postTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'passing_grade' => 80, 'attempt_limit' => 3]);
        $preTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 40, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 60, 'passed' => false, 'submitted_at' => now()]);

        $this->actingAs($admin)->get(route('reports.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('enrollments.data.0.has_course_assessment', false)
            ->where('enrollments.data.0.pre_test_score', null)
            ->where('enrollments.data.0.best_post_score', null)
            ->where('enrollments.data.0.post_test_attempts', 0)
            ->where('enrollments.data.0.assessment_summary', [
                'score' => 50,
                'completed_modules' => 1,
                'total_modules' => 1,
                'is_final' => true,
            ]));
    }

    public function test_export_marks_course_level_evaluation_as_unused_when_assessments_only_exist_per_module(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        $admin = $this->reportAdmin();
        $employee = User::factory()->create(['employee_id' => 'KGJ-005']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-MODULE-002', 'title' => 'Evaluasi per modul', 'status' => 'published']);
        $enrollment = $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $module = $course->modules()->create(['title' => 'Dasar penjualan', 'sequence' => 1]);
        $preTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 1]);
        $postTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'passing_grade' => 80, 'attempt_limit' => 3]);
        $preTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 40, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 60, 'passed' => false, 'submitted_at' => now()]);
        $export = $admin->reportExports()->create(['filters' => [], 'status' => 'queued']);

        (new GenerateReportExport($export->id))->handle();

        $contents = Storage::disk('s3')->get($export->fresh()->path);
        $this->assertStringContainsString('Nilai course modul', $contents);
        $this->assertStringContainsString('Nilai akhir', $contents);
        $this->assertStringContainsString('Skor Pre-Test course', $contents);
        $this->assertStringContainsString('Tidak digunakan', $contents);
    }

    public function test_report_filters_enrollments_waiting_for_final_review(): void
    {
        $admin = $this->reportAdmin();
        $employee = User::factory()->create(['name' => 'Ayu Wijaya']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-REVIEW-001', 'title' => 'Evaluasi akhir', 'status' => 'published']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'pending_review']);

        $this->actingAs($admin)->get(route('reports.index', ['status' => 'pending_review']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'pending_review')
                ->has('enrollments.data', 1)
                ->where('enrollments.data.0.status', 'pending_review'));
    }

    private function reportAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
