<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TeamProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_only_see_direct_reports(): void
    {
        $permission = Permission::query()->create(['slug' => 'view-team', 'name' => 'Lihat progres tim']);
        $role = Role::query()->create(['slug' => 'manager', 'name' => 'Manager']);
        $role->permissions()->attach($permission);
        $manager = User::factory()->create();
        $manager->roles()->attach($role);
        $directReport = User::factory()->create(['name' => 'Dewi Anggraini', 'manager_id' => $manager->id]);
        $otherEmployee = User::factory()->create(['name' => 'Bima Pratama']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $directReport->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $otherEmployee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs(User::factory()->create())->get(route('team-progress.index'))->assertForbidden();

        $this->actingAs($manager)->get(route('team-progress.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('team/Index')
            ->has('enrollments.data', 1)
            ->where('enrollments.data.0.user.name', 'Dewi Anggraini'));
    }
}
