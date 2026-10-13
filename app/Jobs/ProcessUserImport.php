<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\UserImport;
use App\Services\LearningPathAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessUserImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $userImportId) {}

    public function handle(): void
    {
        $import = UserImport::query()->findOrFail($this->userImportId);
        $password = config('imports.initial_password');

        if (blank($password)) {
            $this->finish($import, 'failed', 0, 0, 0, [['line' => 0, 'message' => 'IMPORT_INITIAL_PASSWORD belum dikonfigurasi.']]);

            return;
        }

        $stream = Storage::disk('local')->readStream($import->path);
        abort_unless(is_resource($stream), 422, 'File impor tidak dapat dibaca.');
        $import->update(['status' => 'processing', 'errors' => []]);

        try {
            $headers = fgetcsv($stream);
            $headers = is_array($headers) ? array_map(fn ($header) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))), $headers) : [];
            $required = ['employee_id', 'name', 'email'];
            if (array_diff($required, $headers) || count($headers) !== count(array_unique($headers))) {
                $this->finish($import, 'failed', 0, 0, 0, [['line' => 1, 'message' => 'Header wajib: employee_id, name, email. Header tidak boleh duplikat.']]);

                return;
            }

            $role = Role::query()->where('slug', 'employee')->first();
            if (! $role) {
                $this->finish($import, 'failed', 0, 0, 0, [['line' => 0, 'message' => 'Role employee belum tersedia.']]);

                return;
            }

            $catalog = $this->catalog();
            $total = $imported = $skipped = 0;
            $errors = [];
            $rows = [];
            $seenEmployeeIds = [];
            $seenEmails = [];

            while (($values = fgetcsv($stream)) !== false) {
                $total++;
                if ($values === [null] || $values === []) {
                    continue;
                }
                $rows[] = ['line' => $total + 1, 'data' => array_combine($headers, array_pad($values, count($headers), '')) ?: []];
                if (count($rows) === 200) {
                    [$imported, $skipped, $errors] = $this->process($rows, $role, $password, $catalog, $seenEmployeeIds, $seenEmails, $imported, $skipped, $errors);
                    $rows = [];
                }
            }

            if ($rows) {
                [$imported, $skipped, $errors] = $this->process($rows, $role, $password, $catalog, $seenEmployeeIds, $seenEmails, $imported, $skipped, $errors);
            }

            $this->finish($import, 'completed', $total, $imported, $skipped, $errors);
        } finally {
            fclose($stream);
            Storage::disk('local')->delete($import->path);
        }
    }

    public function failed(Throwable $exception): void
    {
        UserImport::query()->whereKey($this->userImportId)->update(['status' => 'failed', 'errors' => [['line' => 0, 'message' => 'Impor gagal diproses. Silakan unggah ulang file.']], 'completed_at' => now()]);
    }

    private function process(array $rows, Role $role, string $password, array $catalog, array &$seenEmployeeIds, array &$seenEmails, int $imported, int $skipped, array $errors): array
    {
        $employeeIds = collect($rows)->map(fn ($row) => trim((string) ($row['data']['employee_id'] ?? '')))->filter()->all();
        $emails = collect($rows)->map(fn ($row) => strtolower(trim((string) ($row['data']['email'] ?? ''))))->filter()->all();
        $existing = User::query()->whereIn('employee_id', $employeeIds)->orWhereIn('email', $emails)->get(['employee_id', 'email']);
        foreach ($existing as $user) {
            $seenEmployeeIds[$user->employee_id] = true;
            $seenEmails[strtolower($user->email)] = true;
        }
        $managerIds = User::query()->whereIn('employee_id', collect($rows)->map(fn ($row) => trim((string) ($row['data']['manager_employee_id'] ?? '')))->filter()->all())->pluck('id', 'employee_id')->all();

        foreach ($rows as $row) {
            $data = array_map(fn ($value) => trim((string) $value), $row['data']);
            $employeeId = $data['employee_id'] ?? '';
            $email = strtolower($data['email'] ?? '');
            $status = $data['status'] ?? 'active';
            $message = blank($employeeId) || blank($data['name'] ?? '') || blank($email) ? 'employee_id, name, dan email wajib diisi.' : null;
            $message ??= ! filter_var($email, FILTER_VALIDATE_EMAIL) ? 'Format email tidak valid.' : null;
            $message ??= isset($seenEmployeeIds[$employeeId]) || isset($seenEmails[$email]) ? 'employee_id atau email sudah terdaftar; baris dilewati.' : null;
            $message ??= ! in_array($status, ['active', 'inactive'], true) ? 'Status hanya boleh active atau inactive.' : null;
            $division = blank($data['division_code'] ?? '') ? null : ($catalog['divisions'][strtoupper($data['division_code'])] ?? null);
            $branch = blank($data['branch_code'] ?? '') ? null : ($catalog['branches'][strtoupper($data['branch_code'])] ?? null);
            $message ??= blank($data['division_code'] ?? '') || $division ? null : 'Kode divisi tidak ditemukan atau tidak aktif.';
            $message ??= blank($data['branch_code'] ?? '') || $branch ? null : 'Kode cabang tidak ditemukan atau tidak aktif.';
            $positionName = strtolower($data['position_name'] ?? '');
            $position = blank($positionName) ? null : ($division ? ($catalog['positions'][$division->id.'|'.$positionName] ?? null) : null);
            $message ??= blank($positionName) || $position ? null : 'Jabatan tidak ditemukan pada divisi yang dipilih.';
            $managerId = blank($data['manager_employee_id'] ?? '') ? null : ($managerIds[$data['manager_employee_id']] ?? null);
            $message ??= blank($data['manager_employee_id'] ?? '') || $managerId ? null : 'Manager harus sudah terdaftar sebelum impor.';

            if ($message) {
                $skipped++;
                if (count($errors) < 100) {
                    $errors[] = ['line' => $row['line'], 'message' => $message];
                }

                continue;
            }

            $user = DB::transaction(function () use ($employeeId, $data, $email, $status, $division, $position, $branch, $managerId, $password, $role): User {
                $user = User::query()->create(['employee_id' => $employeeId, 'name' => $data['name'], 'email' => $email, 'phone' => $data['phone'] ?? null, 'password' => $password, 'division_id' => $division?->id, 'position_id' => $position?->id, 'branch_id' => $branch?->id, 'manager_id' => $managerId, 'status' => $status, 'email_verified_at' => now()]);
                $user->roles()->attach($role);
                app(LearningPathAssignmentService::class)->assignMatching($user);

                return $user;
            });
            $seenEmployeeIds[$employeeId] = true;
            $seenEmails[$email] = true;
            $managerIds[$employeeId] = $user->id;
            $imported++;
        }

        return [$imported, $skipped, $errors];
    }

    private function catalog(): array
    {
        $divisions = Division::query()->where('is_active', true)->get(['id', 'code'])->keyBy(fn (Division $division) => strtoupper($division->code))->all();
        $branches = Branch::query()->where('is_active', true)->get(['id', 'code'])->keyBy(fn (Branch $branch) => strtoupper($branch->code))->all();
        $positions = Position::query()->where('is_active', true)->get(['id', 'division_id', 'name'])->keyBy(fn (Position $position) => $position->division_id.'|'.strtolower($position->name))->all();

        return compact('divisions', 'branches', 'positions');
    }

    private function finish(UserImport $import, string $status, int $total, int $imported, int $skipped, array $errors): void
    {
        $import->update(['status' => $status, 'total_rows' => $total, 'imported_rows' => $imported, 'skipped_rows' => $skipped, 'errors' => $errors, 'completed_at' => now()]);
    }
}
