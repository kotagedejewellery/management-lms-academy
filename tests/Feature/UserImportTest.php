<?php

namespace Tests\Feature;

use App\Jobs\ProcessUserImport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_queues_csv_import_and_duplicate_rows_are_skipped(): void
    {
        config()->set('imports.initial_password', 'InitialPass123!');
        Storage::fake('local');
        Queue::fake();
        $admin = $this->userWithPermission('manage-users');
        Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        User::factory()->create(['employee_id' => 'KGJ-001', 'email' => 'existing@example.test']);
        $file = UploadedFile::fake()->createWithContent('employees.csv', "employee_id,name,email\nKGJ-001,Existing,existing@example.test\nKGJ-002,Employee Two,two@example.test\n");

        $this->actingAs($admin)->post(route('user-imports.store'), ['file' => $file])->assertRedirect(route('user-imports.index'));
        $import = UserImport::query()->firstOrFail();
        Queue::assertPushed(ProcessUserImport::class, fn (ProcessUserImport $job) => $job->userImportId === $import->id);
        (new ProcessUserImport($import->id))->handle();

        $this->assertDatabaseHas('users', ['employee_id' => 'KGJ-002', 'email' => 'two@example.test']);
        $this->assertSame('completed', $import->fresh()->status);
        $this->assertSame(1, $import->fresh()->imported_rows);
        $this->assertSame(1, $import->fresh()->skipped_rows);
    }

    public function test_employee_cannot_start_user_import(): void
    {
        $this->actingAs(User::factory()->create())->get(route('user-imports.index'))->assertForbidden();
    }

    private function userWithPermission(string $permission): User
    {
        $permission = Permission::query()->create(['slug' => $permission, 'name' => $permission]);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
