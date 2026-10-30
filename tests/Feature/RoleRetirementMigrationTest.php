<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RoleRetirementMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reassigns_retired_role_accounts_without_removing_users_or_learning_data(): void
    {
        $employee = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $lmsAdmin = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin / HR']);
        $manager = Role::query()->create(['slug' => 'manager', 'name' => 'Manager Tim']);
        $management = Role::query()->create(['slug' => 'management', 'name' => 'Management / Owner']);
        $managerAccount = User::factory()->create();
        $managementAccount = User::factory()->create();
        $directReport = User::factory()->create(['manager_id' => $managerAccount->id]);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ROLE-001', 'title' => 'Retire role', 'status' => 'published']);
        $enrollment = Enrollment::query()->create(['user_id' => $managerAccount->id, 'course_id' => $course->id, 'status' => 'active']);

        $managerAccount->roles()->attach($manager);
        $managementAccount->roles()->attach($management);

        $migration = require database_path('migrations/2026_10_30_000001_retire_manager_and_management_roles.php');
        $migration->up();

        $this->assertDatabaseMissing('roles', ['slug' => 'manager']);
        $this->assertDatabaseMissing('roles', ['slug' => 'management']);
        $this->assertDatabaseHas('user_roles', ['user_id' => $managerAccount->id, 'role_id' => $employee->id]);
        $this->assertDatabaseHas('user_roles', ['user_id' => $managementAccount->id, 'role_id' => $lmsAdmin->id]);
        $this->assertDatabaseHas('users', ['id' => $directReport->id, 'manager_id' => $managerAccount->id]);
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'user_id' => $managerAccount->id]);
    }

    public function test_it_does_not_remove_a_retired_role_when_its_replacement_is_missing(): void
    {
        $management = Role::query()->create(['slug' => 'management', 'name' => 'Management / Owner']);
        $account = User::factory()->create();
        $account->roles()->attach($management);

        $migration = require database_path('migrations/2026_10_30_000001_retire_manager_and_management_roles.php');

        try {
            $migration->up();
            $this->fail('Migration should reject a missing replacement role.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('role pengganti', $exception->getMessage());
        }

        $this->assertDatabaseHas('roles', ['id' => $management->id, 'slug' => 'management']);
        $this->assertDatabaseHas('user_roles', ['user_id' => $account->id, 'role_id' => $management->id]);
    }

    public function test_it_does_not_offer_a_lossy_rollback(): void
    {
        $migration = require database_path('migrations/2026_10_30_000001_retire_manager_and_management_roles.php');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('forward-only');

        $migration->down();
    }
}
