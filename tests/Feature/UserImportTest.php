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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class UserImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_queues_csv_import_and_duplicate_rows_are_skipped(): void
    {
        config()->set('imports.initial_password', 'InitialPass123!');
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
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

    public function test_admin_can_download_the_official_user_import_template(): void
    {
        $admin = $this->userWithPermission('manage-users');

        $this->actingAs($admin)->get(route('user-imports.template'))
            ->assertOk()
            ->assertDownload('template-import-employee.csv');
    }

    public function test_admin_can_import_xlsx_with_hired_date(): void
    {
        config()->set('imports.initial_password', 'InitialPass123!');
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        Queue::fake();
        $admin = $this->userWithPermission('manage-users');
        Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['employee_id', 'name', 'email', 'hired_at'],
            ['KGJ-003', 'Employee Three', 'three@example.test', '2026-01-15'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'kgj-import-');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        $file = new UploadedFile($path, 'employees.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($admin)->post(route('user-imports.store'), ['file' => $file])->assertRedirect(route('user-imports.index'));
        (new ProcessUserImport(UserImport::query()->sole()->id))->handle();

        $this->assertDatabaseHas('users', ['employee_id' => 'KGJ-003', 'hired_at' => '2026-01-15 00:00:00']);
        @unlink($path);
    }

    public function test_xlsx_import_processes_rows_across_the_chunk_boundary(): void
    {
        config()->set('imports.initial_password', 'InitialPass123!');
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        Queue::fake();
        $admin = $this->userWithPermission('manage-users');
        Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        $spreadsheet = new Spreadsheet;
        $rows = [['employee_id', 'name', 'email']];
        foreach (range(1, 201) as $row) {
            $rows[] = ['KGJ-'.str_pad((string) $row, 4, '0', STR_PAD_LEFT), 'Employee '.$row, "employee{$row}@example.test"];
        }
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'kgj-import-');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        $file = new UploadedFile($path, 'employees.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($admin)->post(route('user-imports.store'), ['file' => $file])->assertRedirect(route('user-imports.index'));
        (new ProcessUserImport(UserImport::query()->sole()->id))->handle();

        $this->assertSame(201, User::query()->where('employee_id', 'like', 'KGJ-%')->count());
        @unlink($path);
    }

    public function test_xlsx_import_rejects_a_workbook_with_more_than_five_thousand_data_rows(): void
    {
        config()->set('imports.initial_password', 'InitialPass123!');
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        $admin = $this->userWithPermission('manage-users');
        Role::query()->create(['slug' => 'employee', 'name' => 'Employee']);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['employee_id', 'name', 'email']], null, 'A1');
        // The preflight check reads worksheet dimensions, so one distant cell proves
        // the limit without retaining thousands of spreadsheet cells in memory.
        $sheet->setCellValue('A5002', 'KGJ-05001');
        $path = tempnam(sys_get_temp_dir(), 'kgj-import-');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $file = UploadedFile::fake()->createWithContent('employees.xlsx', $contents);

        $this->actingAs($admin)->post(route('user-imports.store'), ['file' => $file])->assertRedirect(route('user-imports.index'));
        (new ProcessUserImport(UserImport::query()->sole()->id))->handle();

        $this->assertSame('failed', UserImport::query()->sole()->status);
        $this->assertSame('File XLSX maksimal berisi 5.000 baris data.', UserImport::query()->sole()->errors[0]['message']);
        $this->assertSame(0, User::query()->where('employee_id', 'like', 'KGJ-%')->count());
        @unlink($path);
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
