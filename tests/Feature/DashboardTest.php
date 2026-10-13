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
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_see_their_learning_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('mode', 'learner')
            ->has('stats', 2)
            ->where('continue_course', null));
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
                ->where('mode', 'management')
                ->where('stats.1.value', 1)
                ->where('stats.2.value', 1));
    }

    private function managementAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
