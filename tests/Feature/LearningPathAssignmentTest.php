<?php

namespace Tests\Feature;

use App\Jobs\SyncLearningPathRule;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\LearningPath;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearningPathAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_assigns_path_and_learner_receives_its_course_enrollment(): void
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);
        $learner = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $path = LearningPath::query()->create(['name' => 'Path Onboarding', 'status' => 'published']);
        $path->pathCourses()->create(['course_id' => $course->id, 'sequence' => 1]);

        $this->actingAs($admin)->post(route('learning-path-assignments.store'), ['user_id' => $learner->id, 'learning_path_id' => $path->id])->assertRedirect();
        $this->actingAs($learner)->get(route('my-learning-paths.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('learning/Paths')
            ->has('paths.data', 1)
            ->where('paths.data.0.courses.0.enrollment_status', 'active'));
        $this->assertDatabaseHas('enrollments', ['user_id' => $learner->id, 'course_id' => $course->id, 'status' => 'active']);
    }

    public function test_matching_rule_assigns_path_and_courses_when_an_employee_is_created(): void
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $userPermission = Permission::query()->create(['slug' => 'manage-users', 'name' => 'Kelola user']);
        $adminRole = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $adminRole->permissions()->attach([$permission->id, $userPermission->id]);
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        $division = Division::query()->create(['code' => 'SLS', 'name' => 'Sales', 'is_active' => true]);
        $position = Position::query()->create(['division_id' => $division->id, 'name' => 'Sales Associate', 'is_active' => true]);
        $branch = Branch::query()->create(['code' => 'KTG', 'name' => 'Kotagede', 'is_active' => true]);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $path = LearningPath::query()->create(['name' => 'Path Sales', 'status' => 'published']);
        $path->pathCourses()->create(['course_id' => $course->id, 'sequence' => 1]);

        $this->actingAs($admin)->post(route('learning-path-assignment-rules.store'), [
            'learning_path_id' => $path->id,
            'division_id' => $division->id,
            'position_id' => $position->id,
            'branch_id' => $branch->id,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('users.store'), [
            'employee_id' => 'KGJ-101',
            'name' => 'Employee Sales',
            'email' => 'sales@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $employeeRole->id,
            'division_id' => $division->id,
            'position_id' => $position->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ])->assertRedirect(route('users.index'));

        $learner = User::query()->where('email', 'sales@example.test')->sole();
        $this->assertDatabaseHas('learning_path_assignments', ['user_id' => $learner->id, 'learning_path_id' => $path->id]);
        $this->assertDatabaseHas('enrollments', ['user_id' => $learner->id, 'course_id' => $course->id, 'status' => 'active']);
    }

    public function test_later_mandatory_course_in_an_assigned_path_is_locked_until_the_previous_course_is_completed(): void
    {
        $learner = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $first = Course::query()->create(['category_id' => $category->id, 'code' => 'PATH-001', 'title' => 'Pertama', 'is_mandatory' => true, 'status' => 'published']);
        $first->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $second = Course::query()->create(['category_id' => $category->id, 'code' => 'PATH-002', 'title' => 'Kedua', 'is_mandatory' => true, 'status' => 'published']);
        $second->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $path = LearningPath::query()->create(['name' => 'Path bertahap', 'status' => 'published']);
        $path->pathCourses()->create(['course_id' => $first->id, 'sequence' => 1]);
        $path->pathCourses()->create(['course_id' => $second->id, 'sequence' => 2]);
        $learner->learningPathAssignments()->create(['learning_path_id' => $path->id]);
        $learner->enrollments()->create(['course_id' => $first->id]);
        $learner->enrollments()->create(['course_id' => $second->id]);

        $this->actingAs($learner)->get(route('my-learning.show', $second))->assertForbidden();

        $learner->enrollments()->where('course_id', $first->id)->update(['status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($learner)->get(route('my-learning.show', $second))->assertOk();
    }

    public function test_assignee_search_returns_only_matching_active_employee_fields(): void
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);
        $match = User::factory()->create(['name' => 'Sari Kotagede', 'employee_id' => 'KGJ-008', 'status' => 'active']);
        User::factory()->create(['name' => 'Sari Tidak Aktif', 'employee_id' => 'KGJ-009', 'status' => 'inactive']);

        $this->actingAs($admin)->getJson(route('assignees.search', ['q' => 'Sari']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $match->id)
            ->assertJsonMissingPath('0.email');
    }

    public function test_creating_a_rule_queues_a_safe_sync_for_matching_existing_employees(): void
    {
        Queue::fake();
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);
        $division = Division::query()->create(['code' => 'SLS', 'name' => 'Sales', 'is_active' => true]);
        $position = Position::query()->create(['division_id' => $division->id, 'name' => 'Sales Associate', 'is_active' => true]);
        $branch = Branch::query()->create(['code' => 'KTG', 'name' => 'Kotagede', 'is_active' => true]);
        $path = LearningPath::query()->create(['name' => 'Path Sales', 'status' => 'published']);
        User::factory()->create(['division_id' => $division->id, 'position_id' => $position->id, 'branch_id' => $branch->id, 'status' => 'active']);

        $this->actingAs($admin)->post(route('learning-path-assignment-rules.store'), [
            'learning_path_id' => $path->id,
            'division_id' => $division->id,
            'position_id' => $position->id,
            'branch_id' => $branch->id,
        ])->assertRedirect();

        Queue::assertPushed(SyncLearningPathRule::class);
    }
}
