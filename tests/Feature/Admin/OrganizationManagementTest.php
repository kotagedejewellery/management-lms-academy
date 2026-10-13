<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_admin_can_manage_organization_master_data(): void
    {
        $admin = $this->organizationAdmin();

        $this->actingAs($admin)->post(route('organization.store', 'divisions'), [
            'code' => 'OPS',
            'name' => 'Operasional',
            'is_active' => true,
        ])->assertRedirect(route('organization.index', 'divisions'));

        $divisionId = (int) \DB::table('divisions')->value('id');

        $this->actingAs($admin)->post(route('organization.store', 'positions'), [
            'division_id' => $divisionId,
            'name' => 'Supervisor',
            'is_active' => true,
        ])->assertRedirect(route('organization.index', 'positions'));

        $this->assertDatabaseHas('positions', ['division_id' => $divisionId, 'name' => 'Supervisor']);

        $this->actingAs($admin)->put(route('organization.update', ['type' => 'divisions', 'record' => $divisionId]), [
            'code' => 'OPS',
            'name' => 'Operasional',
            'is_active' => false,
        ])->assertRedirect(route('organization.index', 'divisions'));

        $this->assertDatabaseHas('divisions', ['id' => $divisionId, 'is_active' => false]);
    }

    public function test_employee_cannot_access_organization_master_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('organization.index', 'divisions'))
            ->assertForbidden();
    }

    private function organizationAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-organization', 'name' => 'Manage Organization']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin / HR']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
