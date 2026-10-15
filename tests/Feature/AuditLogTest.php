<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_review_administrative_activity_without_sensitive_request_data(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::query()->create(['slug' => 'super-admin', 'name' => 'Super Admin']));
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);

        $this->actingAs($superAdmin)->post(route('users.store'), [
            'employee_id' => 'KGJ-021', 'name' => 'Employee One', 'email' => 'employee.one@example.test', 'password' => 'password', 'password_confirmation' => 'password', 'role_id' => $employeeRole->id, 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['actor_id' => $superAdmin->id, 'event' => 'users.store']);
        $this->assertNull(ActivityLog::query()->firstOrFail()->properties);
        $this->actingAs($superAdmin)->get(route('audit-logs.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('audit-logs/Index')->has('activities.data', 1));
    }

    public function test_non_super_admin_cannot_view_audit_activity(): void
    {
        $this->actingAs(User::factory()->create())->get(route('audit-logs.index'))->assertForbidden();
    }

    public function test_audit_log_records_safe_before_and_after_values_for_administrative_updates(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::query()->create(['slug' => 'super-admin', 'name' => 'Super Admin']));
        $employeeRole = Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        $employee = User::factory()->create([
            'employee_id' => 'KGJ-022',
            'name' => 'Employee Before',
            'email' => 'employee.before@example.test',
            'phone' => '0812345678',
            'status' => 'active',
        ]);
        $employee->roles()->attach($employeeRole);

        $this->actingAs($superAdmin)->put(route('users.update', $employee), [
            'employee_id' => $employee->employee_id,
            'name' => 'Employee After',
            'email' => $employee->email,
            'phone' => $employee->phone,
            'role_id' => $employeeRole->id,
            'status' => 'inactive',
        ])->assertRedirect();

        $activity = ActivityLog::query()->where('event', 'users.update')->sole();

        $this->assertSame('Employee Before', $activity->properties['changes']['name']['before']);
        $this->assertSame('Employee After', $activity->properties['changes']['name']['after']);
        $this->assertSame('active', $activity->properties['changes']['status']['before']);
        $this->assertSame('inactive', $activity->properties['changes']['status']['after']);
        $this->assertArrayNotHasKey('password', $activity->properties['changes']);
        $this->actingAs($superAdmin)->get(route('audit-logs.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('activities.data.0.changes.name.before', 'Employee Before')
            ->where('activities.data.0.changes.name.after', 'Employee After'));
    }

    public function test_audit_log_records_changes_for_an_organization_route_with_a_numeric_subject(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::query()->create(['slug' => 'super-admin', 'name' => 'Super Admin']));
        $division = Division::query()->create(['code' => 'SALES', 'name' => 'Sales Before', 'is_active' => true]);

        $this->actingAs($superAdmin)->put(route('organization.update', ['type' => 'divisions', 'record' => $division]), [
            'code' => $division->code,
            'name' => 'Sales After',
            'is_active' => true,
        ])->assertRedirect();

        $activity = ActivityLog::query()->where('event', 'organization.update')->sole();

        $this->assertSame(Division::class, $activity->subject_type);
        $this->assertSame($division->id, $activity->subject_id);
        $this->assertSame('Sales Before', $activity->properties['changes']['name']['before']);
        $this->assertSame('Sales After', $activity->properties['changes']['name']['after']);
    }
}
