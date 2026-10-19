<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\UserImport;
use App\Services\LearningPathAssignmentService;
use App\Support\SpreadsheetChunkReadFilter;
use Carbon\Carbon;
use Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProcessUserImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_XLSX_DATA_ROWS = 5000;

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

        $import->update(['status' => 'processing', 'errors' => []]);

        try {
            $xlsxDataRows = $this->xlsxDataRowCount($import);
            if ($xlsxDataRows > self::MAX_XLSX_DATA_ROWS) {
                $this->finish($import, 'failed', 0, 0, 0, [[
                    'line' => 0,
                    'message' => 'File XLSX maksimal berisi 5.000 baris data.',
                ]]);

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

            $headers = [];
            foreach ($this->rows($import, $xlsxDataRows) as $line => $values) {
                if ($line === 1) {
                    $headers = array_map(fn ($header) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))), $values);
                    $required = ['employee_id', 'name', 'email'];
                    if (array_diff($required, $headers) || count($headers) !== count(array_unique($headers))) {
                        $this->finish($import, 'failed', 0, 0, 0, [['line' => 1, 'message' => 'Header wajib: employee_id, name, email. Header tidak boleh duplikat.']]);

                        return;
                    }

                    continue;
                }
                $total++;
                if (! array_filter($values, fn ($value) => filled($value))) {
                    continue;
                }
                $rows[] = [
                    'line' => $line,
                    'data' => array_map(
                        fn ($value) => $value === null ? null : (string) $value,
                        array_combine($headers, array_pad($values, count($headers), '')) ?: [],
                    ),
                ];
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
            Storage::disk()->delete($import->path);
        }
    }

    public function failed(Throwable $exception): void
    {
        UserImport::query()->whereKey($this->userImportId)->update(['status' => 'failed', 'errors' => [['line' => 0, 'message' => 'Impor gagal diproses. Silakan unggah ulang file.']], 'completed_at' => now()]);
    }

    /**
     * @param  list<array{line: int, data: array<string, string|null>}>  $rows
     * @param  array{divisions: array<string, Division>, branches: array<string, Branch>, positions: array<string, Position>}  $catalog
     * @param  array<string, true>  $seenEmployeeIds
     * @param  array<string, true>  $seenEmails
     * @param  list<array{line: int, message: string}>  $errors
     * @return array{int, int, list<array{line: int, message: string}>}
     */
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
            $hiredAt = blank($data['hired_at'] ?? '') ? null : $data['hired_at'];
            $message = blank($employeeId) || blank($data['name'] ?? '') || blank($email) ? 'employee_id, name, dan email wajib diisi.' : null;
            $message ??= ! filter_var($email, FILTER_VALIDATE_EMAIL) ? 'Format email tidak valid.' : null;
            $message ??= isset($seenEmployeeIds[$employeeId]) || isset($seenEmails[$email]) ? 'employee_id atau email sudah terdaftar; baris dilewati.' : null;
            $message ??= ! in_array($status, ['active', 'inactive'], true) ? 'Status hanya boleh active atau inactive.' : null;
            $message ??= $hiredAt === null || Carbon::hasFormat($hiredAt, 'Y-m-d') ? null : 'Tanggal masuk harus memakai format YYYY-MM-DD.';
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

            $user = DB::transaction(function () use ($employeeId, $data, $email, $status, $hiredAt, $division, $position, $branch, $managerId, $password, $role): User {
                $user = User::query()->create(['employee_id' => $employeeId, 'hired_at' => $hiredAt, 'name' => $data['name'], 'email' => $email, 'phone' => $data['phone'] ?? null, 'password' => $password, 'division_id' => $division?->id, 'position_id' => $position?->id, 'branch_id' => $branch?->id, 'manager_id' => $managerId, 'status' => $status, 'email_verified_at' => null]);
                $user->roles()->attach($role);
                app(LearningPathAssignmentService::class)->assignMatching($user);

                return $user;
            });
            $user->sendEmailVerificationNotification();
            $seenEmployeeIds[$employeeId] = true;
            $seenEmails[$email] = true;
            $managerIds[$employeeId] = $user->id;
            $imported++;
        }

        return [$imported, $skipped, $errors];
    }

    /** @return Generator<int, list<string|int|float|null>> */
    private function rows(UserImport $import, int $xlsxDataRows): Generator
    {
        if (strtolower(pathinfo($import->original_name, PATHINFO_EXTENSION)) === 'xlsx') {
            $temporaryPath = $this->temporaryPath($import);

            try {
                $lastRow = $xlsxDataRows + 1;
                for ($startRow = 1; $startRow <= $lastRow; $startRow += 200) {
                    $endRow = min($lastRow, $startRow + 199);
                    $reader = IOFactory::createReaderForFile($temporaryPath);
                    $reader->setReadDataOnly(true);
                    $reader->setReadFilter(new SpreadsheetChunkReadFilter($startRow, $endRow));
                    $spreadsheet = $reader->load($temporaryPath);

                    try {
                        $sheet = $spreadsheet->getActiveSheet();
                        foreach ($sheet->getRowIterator($startRow, $endRow) as $row) {
                            $values = [];
                            foreach ($row->getCellIterator() as $cell) {
                                $values[] = $cell->getFormattedValue();
                            }
                            yield $row->getRowIndex() => $values;
                        }
                    } finally {
                        $spreadsheet->disconnectWorksheets();
                    }
                }
            } finally {
                @unlink($temporaryPath);
            }

            return;
        }

        $stream = Storage::disk()->readStream($import->path);
        abort_unless(is_resource($stream), 422, 'File impor tidak dapat dibaca.');
        try {
            $line = 0;
            while (($values = fgetcsv($stream)) !== false) {
                yield ++$line => $values;
            }
        } finally {
            fclose($stream);
        }
    }

    private function xlsxDataRowCount(UserImport $import): int
    {
        if (strtolower(pathinfo($import->original_name, PATHINFO_EXTENSION)) !== 'xlsx') {
            return 0;
        }

        $temporaryPath = $this->temporaryPath($import);

        try {
            $worksheets = IOFactory::createReaderForFile($temporaryPath)->listWorksheetInfo($temporaryPath);

            return max(0, ...array_map(
                fn (array $worksheet): int => max(0, $worksheet['totalRows'] - 1),
                $worksheets,
            ));
        } finally {
            @unlink($temporaryPath);
        }
    }

    private function temporaryPath(UserImport $import): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'kgj-user-import-');
        $stream = Storage::disk()->readStream($import->path);
        $output = $temporaryPath === false ? false : fopen($temporaryPath, 'wb');

        if (! is_resource($stream) || ! is_resource($output)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            if ($temporaryPath !== false) {
                @unlink($temporaryPath);
            }

            abort(422, 'File impor tidak dapat dipersiapkan.');
        }

        try {
            stream_copy_to_stream($stream, $output);
        } finally {
            fclose($stream);
            fclose($output);
        }

        return $temporaryPath;
    }

    /** @return array{divisions: array<string, Division>, branches: array<string, Branch>, positions: array<string, Position>} */
    private function catalog(): array
    {
        $divisions = Division::query()->where('is_active', true)->get(['id', 'code'])->keyBy(fn (Division $division) => strtoupper($division->code))->all();
        $branches = Branch::query()->where('is_active', true)->get(['id', 'code'])->keyBy(fn (Branch $branch) => strtoupper($branch->code))->all();
        $positions = Position::query()->where('is_active', true)->get(['id', 'division_id', 'name'])->keyBy(fn (Position $position) => $position->division_id.'|'.strtolower($position->name))->all();

        return compact('divisions', 'branches', 'positions');
    }

    /** @param list<array{line: int, message: string}> $errors */
    private function finish(UserImport $import, string $status, int $total, int $imported, int $skipped, array $errors): void
    {
        $import->update(['status' => $status, 'total_rows' => $total, 'imported_rows' => $imported, 'skipped_rows' => $skipped, 'errors' => $errors, 'completed_at' => now()]);
    }
}
