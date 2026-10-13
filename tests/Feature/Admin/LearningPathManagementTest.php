<?php

namespace Tests\Feature\Admin;

use App\Jobs\SyncLearningPath;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\LearningPath;
use App\Models\LearningPathAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\LearningPathAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LearningPathManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_path_requires_a_published_course_before_it_can_be_published(): void
    {
        $admin = $this->pathAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create([
            'category_id' => $category->id,
            'code' => 'ONB-001',
            'title' => 'Pengenalan KGJ',
            'passing_grade' => 70,
            'status' => 'published',
        ]);
        $secondCourse = Course::query()->create([
            'category_id' => $category->id,
            'code' => 'ONB-002',
            'title' => 'Dasar Layanan KGJ',
            'passing_grade' => 70,
            'status' => 'published',
        ]);

        $this->actingAs($admin)->post(route('learning-paths.store'), [
            'name' => 'Onboarding Sales',
            'status' => 'draft',
        ])->assertRedirect();

        $pathId = (int) \DB::table('learning_paths')->value('id');

        $this->actingAs($admin)->put(route('learning-paths.update', $pathId), [
            'name' => 'Onboarding Sales',
            'status' => 'published',
        ])->assertSessionHasErrors('status');

        $this->actingAs($admin)->post(route('learning-paths.courses.store', $pathId), [
            'course_id' => $course->id,
            'sequence' => 1,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('learning-paths.courses.store', $pathId), [
            'course_id' => $secondCourse->id,
            'sequence' => 2,
        ])->assertRedirect();

        $secondPathCourseId = (int) \DB::table('learning_path_courses')->where('course_id', $secondCourse->id)->value('id');

        $this->actingAs($admin)->put(route('learning-paths.courses.update', [$pathId, $secondPathCourseId]), [
            'course_id' => $secondCourse->id,
            'sequence' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('learning_path_courses', ['course_id' => $secondCourse->id, 'sequence' => 1]);
        $this->assertDatabaseHas('learning_path_courses', ['course_id' => $course->id, 'sequence' => 2]);

        $this->actingAs($admin)->put(route('learning-paths.update', $pathId), [
            'name' => 'Onboarding Sales',
            'status' => 'published',
        ])->assertRedirect(route('learning-paths.content', $pathId));

        $this->assertDatabaseHas('learning_paths', ['id' => $pathId, 'status' => 'published']);
    }

    public function test_employee_cannot_manage_learning_paths(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('learning-paths.index'))
            ->assertForbidden();
    }

    public function test_inserting_course_into_a_path_shifts_existing_courses_and_syncs_published_assignments(): void
    {
        Queue::fake();
        $admin = $this->pathAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $first = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pertama', 'passing_grade' => 70, 'status' => 'published']);
        $second = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-002', 'title' => 'Kedua', 'passing_grade' => 70, 'status' => 'published']);
        $inserted = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-003', 'title' => 'Sisipan', 'passing_grade' => 70, 'status' => 'published']);
        $path = LearningPath::query()->create(['name' => 'Path', 'status' => 'published']);
        $path->pathCourses()->create(['course_id' => $first->id, 'sequence' => 1]);
        $path->pathCourses()->create(['course_id' => $second->id, 'sequence' => 2]);

        $this->actingAs($admin)->post(route('learning-paths.courses.store', $path), [
            'course_id' => $inserted->id,
            'sequence' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('learning_path_courses', ['learning_path_id' => $path->id, 'course_id' => $inserted->id, 'sequence' => 1]);
        $this->assertDatabaseHas('learning_path_courses', ['learning_path_id' => $path->id, 'course_id' => $first->id, 'sequence' => 2]);
        $this->assertDatabaseHas('learning_path_courses', ['learning_path_id' => $path->id, 'course_id' => $second->id, 'sequence' => 3]);
        Queue::assertPushed(SyncLearningPath::class, fn (SyncLearningPath $job) => $job->learningPathId === $path->id);
    }

    public function test_sync_job_enrolls_existing_path_assignees_in_a_new_course(): void
    {
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $first = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-101', 'title' => 'Pertama', 'passing_grade' => 70, 'status' => 'published']);
        $second = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-102', 'title' => 'Kedua', 'passing_grade' => 70, 'status' => 'published']);
        $path = LearningPath::query()->create(['name' => 'Path', 'status' => 'published']);
        $path->pathCourses()->create(['course_id' => $first->id, 'sequence' => 1]);
        $user = User::factory()->create(['status' => 'active']);
        LearningPathAssignment::query()->create(['user_id' => $user->id, 'learning_path_id' => $path->id]);
        $path->pathCourses()->create(['course_id' => $second->id, 'sequence' => 2]);

        (new SyncLearningPath($path->id))->handle(app(LearningPathAssignmentService::class));

        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $second->id, 'status' => 'active']);
    }

    public function test_draft_path_course_can_be_deleted(): void
    {
        $admin = $this->pathAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-DELETE-001', 'title' => 'Course', 'passing_grade' => 70, 'status' => 'published']);
        $path = LearningPath::query()->create(['name' => 'Draft path', 'status' => 'draft']);
        $pathCourse = $path->pathCourses()->create(['course_id' => $course->id, 'sequence' => 1]);

        $this->actingAs($admin)->delete(route('learning-paths.courses.destroy', [$path, $pathCourse]))
            ->assertRedirect(route('learning-paths.content', $path));
        $this->assertDatabaseMissing('learning_path_courses', ['id' => $pathCourse->id]);
    }

    private function pathAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-learning-paths', 'name' => 'Kelola learning path']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
