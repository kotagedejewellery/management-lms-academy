<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_admin_can_set_and_update_assessment_passing_grade(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-004', 'title' => 'Pengenalan', 'passing_grade' => 70]);

        $this->actingAs($admin)->post(route('courses.assessments.store', $course), ['type' => 'post_test', 'passing_grade' => 65, 'time_limit_minutes' => 30])
            ->assertRedirect(route('courses.assessments.index', $course));

        $assessment = Assessment::query()->sole();
        $this->assertSame(65, $assessment->passing_grade);
        $this->assertSame(30, $assessment->time_limit_minutes);

        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 80])
            ->assertRedirect(route('courses.assessments.index', $course));

        $this->assertDatabaseHas('assessments', ['id' => $assessment->id, 'passing_grade' => 80]);
        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 101])->assertSessionHasErrors('passing_grade');
    }

    public function test_course_admin_can_configure_pre_test_requirement_and_attempt_limit_up_to_three(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-005', 'title' => 'Pengenalan', 'passing_grade' => 70]);

        $this->actingAs($admin)->post(route('courses.assessments.store', $course), [
            'type' => 'pre_test',
            'passing_grade' => 0,
            'attempt_limit' => 2,
            'is_required' => true,
        ])->assertRedirect(route('courses.assessments.index', $course));

        $assessment = Assessment::query()->sole();
        $this->assertDatabaseHas('assessments', ['id' => $assessment->id, 'attempt_limit' => 2, 'is_required' => true]);

        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), [
            'passing_grade' => 0,
            'attempt_limit' => 4,
            'is_required' => true,
        ])->assertSessionHasErrors('attempt_limit');
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
