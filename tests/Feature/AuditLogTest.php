<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
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
}
