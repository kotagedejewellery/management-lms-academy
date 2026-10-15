<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\ReportExport;
use App\Support\ReportEnrollmentQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reportExportId) {}

    public function handle(): void
    {
        $export = ReportExport::query()->findOrFail($this->reportExportId);
        $path = "exports/report-{$export->id}.csv";
        $export->update(['status' => 'processing', 'error' => null]);
        $disk = Storage::disk();
        $stream = tmpfile();
        throw_unless(is_resource($stream), RuntimeException::class, 'Tidak dapat membuat file ekspor sementara.');

        try {
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['ID Karyawan', 'Nama Karyawan', 'Kode Course', 'Course', 'Status', 'Skor Pre-Test', 'Skor Post-Test Terbaik', 'Improvement', 'Attempt Post-Test', 'Tenggat', 'Selesai']);
            ReportEnrollmentQuery::make($export->filters)->reorder()->chunkById(500, function ($enrollments) use ($stream): void {
                $enrollments->each(function (Enrollment $enrollment) use ($stream): void {
                    fputcsv($stream, array_map(fn (mixed $value) => $this->csvValue($value), [
                        $enrollment->user?->employee_id,
                        $enrollment->user?->name,
                        $enrollment->course?->code,
                        $enrollment->course?->title,
                        $enrollment->status,
                        $enrollment->pre_test_score,
                        $enrollment->best_post_score,
                        $enrollment->pre_test_score === null || $enrollment->best_post_score === null ? null : (float) $enrollment->best_post_score - (float) $enrollment->pre_test_score,
                        $enrollment->post_test_attempts,
                        $enrollment->due_at?->toDateString(),
                        $enrollment->completed_at?->toDateString(),
                    ]));
                });
            });
            rewind($stream);
            throw_unless($disk->writeStream($path, $stream), RuntimeException::class, 'Tidak dapat menyimpan file ekspor.');
        } catch (Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        } finally {
            fclose($stream);
        }

        $export->update(['status' => 'ready', 'path' => $path, 'completed_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        ReportExport::query()->whereKey($this->reportExportId)->update([
            'status' => 'failed',
            'error' => 'Ekspor gagal diproses. Silakan coba kembali.',
        ]);
    }

    private function csvValue(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'{$value}";
        }

        return $value;
    }
}
