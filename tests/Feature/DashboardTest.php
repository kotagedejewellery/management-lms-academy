<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_authenticated_users_see_their_learning_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('mode', 'learner')
            ->where('title', 'Pembelajaran saya')
            ->has('stats', 3)
            ->has('items', 0));
    }

    public function test_learner_dashboard_limits_continue_items_without_changing_stats(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);

        foreach (range(1, 6) as $number) {
            $course = Course::query()->create(['category_id' => $category->id, 'code' => sprintf('ONB-%03d', $number), 'title' => 'Course '.$number, 'status' => 'published']);
            Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'active']);
        }

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.0.value', 6)
                ->has('items', 5));
    }

    public function test_learner_dashboard_marks_a_course_locked_by_prerequisite_as_unavailable(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $prerequisite = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-PREREQ', 'title' => 'Course Prasyarat', 'status' => 'published']);
        $locked = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-LOCKED', 'title' => 'Course Lanjutan', 'prerequisite_course_id' => $prerequisite->id, 'status' => 'published']);
        Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $prerequisite->id, 'status' => 'active', 'due_at' => today()]);
        Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $locked->id, 'status' => 'active', 'due_at' => today()->addDay()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.1.title', 'Course Lanjutan')
                ->where('items.1.locked', true)
                ->where('items.1.lock_reason', 'Selesaikan course prasyarat terlebih dahulu.')
                ->where('items.1.href', null));
    }

    public function test_management_dashboard_cache_is_invalidated_when_its_source_data_changes(): void
    {
        Cache::forget('dashboard:management');
        $admin = $this->managementAdmin();
        $employee = User::factory()->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has('dashboard:management'));

        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $this->assertFalse(Cache::has('dashboard:management'));

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has('dashboard:management'));

        Enrollment::query()->create(['user_id' => $employee->id, 'course_id' => $course->id, 'status' => 'active']);
        $this->assertFalse(Cache::has('dashboard:management'));

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('mode', 'operations')
                ->where('stats.1.value', 1)
                ->where('stats.2.value', 1));
    }

    public function test_trainer_sees_only_assigned_course_dashboard(): void
    {
        $trainer = $this->userWithPermission('manage-courses', 'trainer');
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $assigned = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-TRAINER', 'title' => 'Course Trainer', 'status' => 'draft']);
        $other = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-OTHER', 'title' => 'Course Lain', 'status' => 'draft']);
        $assigned->trainers()->attach($trainer);

        $this->actingAs($trainer)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('mode', 'trainer')
                ->where('stats.0.value', 1)
                ->has('items', 1)
                ->where('items.0.title', $assigned->title));

        $this->assertSame(2, Course::query()->count());
        $this->assertTrue($other->exists);
    }

    public function test_manager_and_executive_receive_their_own_dashboard_contexts(): void
    {
        $manager = $this->userWithPermission('view-team', 'manager');
        User::factory()->create(['manager_id' => $manager->id]);
        $executive = $this->userWithPermission('view-reports', 'management');

        $this->actingAs($manager)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('mode', 'manager')
                ->where('stats.0.value', 1));
        $this->actingAs($executive)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('mode', 'executive')
                ->where('primary_action.href', '/reports'));
    }

    private function managementAdmin(): User
    {
        return $this->userWithPermission('manage-enrollments', 'lms-admin');
    }

    private function userWithPermission(string $permissionSlug, string $roleSlug): User
    {
        $permission = Permission::query()->create(['slug' => $permissionSlug, 'name' => 'Izin '.$permissionSlug]);
        $role = Role::query()->create(['slug' => $roleSlug, 'name' => $roleSlug]);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
