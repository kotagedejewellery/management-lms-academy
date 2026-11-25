<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_admin_can_create_an_employee(): void
    {
        Notification::fake();

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
        $employee = User::query()->where('email', 'employee.one@example.test')->sole();
        $this->assertNull($employee->email_verified_at);
        Notification::assertSentTo($employee, VerifyEmail::class);
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

    public function test_manager_options_only_include_active_users_from_the_selected_organization(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operations']);
        $central = Branch::query()->create(['code' => 'CTR', 'name' => 'Central', 'is_active' => true]);
        $store = Branch::query()->create(['code' => 'STR', 'name' => 'Store', 'is_active' => true]);

        $eligibleManager = User::factory()->create([
            'name' => 'Eligible Manager',
            'employee_id' => 'KGJ-010',
            'division_id' => $sales->id,
            'branch_id' => $central->id,
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Inactive Manager',
            'division_id' => $sales->id,
            'status' => 'inactive',
        ]);
        User::factory()->create([
            'name' => 'Operations Manager',
            'division_id' => $operations->id,
            'status' => 'active',
        ]);
        $editedUser = User::factory()->create([
            'name' => 'Edited User',
            'division_id' => $sales->id,
            'branch_id' => $store->id,
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Direct Report',
            'division_id' => $sales->id,
            'manager_id' => $editedUser->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->getJson(route('users.manager-options', [
                'division_id' => $sales->id,
                'branch_id' => $store->id,
                'exclude_user_id' => $editedUser->id,
            ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $eligibleManager->id)
            ->assertJsonPath('0.employee_id', 'KGJ-010');
    }

    public function test_user_cannot_be_assigned_a_manager_from_another_division(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operations']);
        $operationsManager = User::factory()->create([
            'division_id' => $operations->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Sales Employee',
            'email' => 'sales.employee@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $employeeRole->id,
            'division_id' => $sales->id,
            'manager_id' => $operationsManager->id,
            'status' => 'active',
        ])->assertSessionHasErrors('manager_id');
    }

    public function test_user_can_be_assigned_a_manager_from_another_branch_in_the_same_division(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $central = Branch::query()->create(['code' => 'CTR', 'name' => 'Central', 'is_active' => true]);
        $store = Branch::query()->create(['code' => 'STR', 'name' => 'Store', 'is_active' => true]);
        $manager = User::factory()->create([
            'division_id' => $sales->id,
            'branch_id' => $central->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Sales Employee',
            'email' => 'sales.employee@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $employeeRole->id,
            'division_id' => $sales->id,
            'branch_id' => $store->id,
            'manager_id' => $manager->id,
            'status' => 'active',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sales.employee@example.test',
            'manager_id' => $manager->id,
        ]);
    }

    public function test_user_cannot_be_assigned_a_direct_report_as_manager(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $manager = User::factory()->create(['division_id' => $sales->id, 'status' => 'active']);
        $directReport = User::factory()->create([
            'division_id' => $sales->id,
            'manager_id' => $manager->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->put(route('users.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $employeeRole->id,
            'division_id' => $sales->id,
            'manager_id' => $directReport->id,
            'status' => 'active',
        ])->assertSessionHasErrors('manager_id');
    }

    public function test_manager_with_direct_reports_cannot_be_deactivated_or_moved_to_another_division(): void
    {
        $admin = $this->userWithPermission('manage-users');
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee / Learner']);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operations']);
        $manager = User::factory()->create(['division_id' => $sales->id, 'status' => 'active']);
        User::factory()->create(['division_id' => $sales->id, 'manager_id' => $manager->id, 'status' => 'active']);

        $payload = [
            'name' => $manager->name,
            'email' => $manager->email,
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $employeeRole->id,
            'division_id' => $sales->id,
            'status' => 'inactive',
        ];

        $this->actingAs($admin)->put(route('users.update', $manager), $payload)
            ->assertSessionHasErrors('status');

        $payload['status'] = 'active';
        $payload['division_id'] = $operations->id;

        $this->actingAs($admin)->put(route('users.update', $manager), $payload)
            ->assertSessionHasErrors('division_id');
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
