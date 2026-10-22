<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrainerProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_can_only_view_progress_for_their_assigned_courses(): void
    {
        $trainer = $this->trainer();
        $otherTrainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $assignedCourse = Course::query()->create(['category_id' => $category->id, 'code' => 'CS-001', 'title' => 'CS Dealmaker Mastery', 'status' => 'published']);
        $otherCourse = Course::query()->create(['category_id' => $category->id, 'code' => 'CS-002', 'title' => 'Product Knowledge', 'status' => 'published']);
        $assignedCourse->trainers()->attach($trainer);
        $otherCourse->trainers()->attach($otherTrainer);

        Enrollment::query()->create(['user_id' => User::factory()->create()->id, 'course_id' => $assignedCourse->id, 'status' => 'active']);
        Enrollment::query()->create(['user_id' => User::factory()->create()->id, 'course_id' => $otherCourse->id, 'status' => 'completed']);

        $this->actingAs($trainer)->get(route('trainer-progress.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('trainer-progress/Index')
                ->where('enrollments.total', 1)
                ->where('enrollments.data.0.course.code', 'CS-001'));

        $this->actingAs($trainer)
            ->get(route('trainer-progress.index', ['course_id' => $otherCourse->id]))
            ->assertNotFound();
    }

    public function test_user_without_course_management_permission_cannot_view_trainer_progress(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('trainer-progress.index'))
            ->assertForbidden();
    }

    public function test_trainer_can_view_module_pre_and_post_test_results_for_an_assigned_course(): void
    {
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'CS-001', 'title' => 'CS Dealmaker Mastery', 'status' => 'published']);
        $course->trainers()->attach($trainer);
        $enrollment = Enrollment::query()->create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id, 'status' => 'active']);
        $module = $course->modules()->create(['title' => 'Membangun kebutuhan pelanggan', 'sequence' => 1]);
        $preTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $postTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);
        $preTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 40, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 55, 'passed' => false, 'submitted_at' => now()]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 2, 'score' => 90, 'passed' => true, 'submitted_at' => now()]);

        $this->actingAs($trainer)->get(route('trainer-progress.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('enrollments.data.0.module_assessments', [[
                    'module' => ['sequence' => 1, 'title' => 'Membangun kebutuhan pelanggan'],
                    'pre_test' => ['score' => 40],
                    'post_test' => ['best_score' => 90, 'attempts' => 2, 'passed' => true],
                ]]));
    }

    public function test_assigned_trainer_can_approve_a_course_ready_for_final_review(): void
    {
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'CS-REVIEW-001', 'title' => 'Evaluasi akhir', 'status' => 'published', 'requires_post_test' => false]);
        $course->trainers()->attach($trainer);
        $enrollment = Enrollment::query()->create([
            'user_id' => User::factory()->create()->id,
            'course_id' => $course->id,
            'status' => 'pending_review',
        ]);

        $this->actingAs($trainer)
            ->post(route('trainer-progress.approve', $enrollment))
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $trainer->id,
            'event' => 'enrollment.final_review_approved',
            'subject_type' => Enrollment::class,
            'subject_id' => $enrollment->id,
        ]);
    }

    public function test_trainer_sees_retake_actions_only_during_final_review(): void
    {
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'CS-RETAKE-001', 'title' => 'Tindak lanjut', 'status' => 'published']);
        $course->trainers()->attach($trainer);
        $enrollment = Enrollment::query()->create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id, 'status' => 'active']);
        $postTest = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 80, 'attempt_limit' => 3]);
        $postTest->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 50, 'passed' => false, 'submitted_at' => now()]);

        $this->actingAs($trainer)->get(route('trainer-progress.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('enrollments.data.0.retake_assessments', []));
    }

    private function trainer(): User
    {
        $permission = Permission::query()->firstOrCreate(['slug' => 'manage-courses'], ['name' => 'Kelola course']);
        $role = Role::query()->firstOrCreate(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
