<?php

namespace Tests\Feature\Admin;

use App\Models\Division;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_admin_can_create_an_employee(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'employee_id' => 'KGJ-001',
            'hired_at' => '2026-01-15',
            'name' => 'Employee One',
            'email' => 'employee.one@example.test',
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $employeeRole->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['employee_id' => 'KGJ-001', 'hired_at' => '2026-01-15 00:00:00', 'email' => 'employee.one@example.test', 'phone' => '08123456789']);
        $this->assertDatabaseHas('user_roles', ['role_id' => $employeeRole->id]);
    }

    public function test_employee_cannot_access_user_management(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_lms_admin_cannot_assign_super_admin_role(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $superAdmin = Role::query()->create(['slug' => 'super-admin', 'name' => 'Super Admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Escalation Attempt',
                'email' => 'escalation@example.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_id' => $superAdmin->id,
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_use_a_position_from_another_division(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operations']);
        $operationsPosition = Position::query()->create(['division_id' => $operations->id, 'name' => 'Admin Operasional']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Employee One',
            'email' => 'employee.one@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $employeeRole->id,
            'division_id' => $sales->id,
            'position_id' => $operationsPosition->id,
            'status' => 'active',
        ])->assertSessionHasErrors('position_id');
    }

    public function test_manager_can_view_only_their_direct_report(): void
    {
        $manager = $this->userWithPermission('view-team');
        $directReport = User::factory()->create(['manager_id' => $manager->id]);
        $otherEmployee = User::factory()->create();

        $this->assertTrue($manager->can('view', $directReport));
        $this->assertFalse($manager->can('view', $otherEmployee));
    }

    private function userWithPermission(string $permission): User
    {
        $permission = Permission::query()->create(['slug' => $permission, 'name' => $permission]);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin / HR']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
