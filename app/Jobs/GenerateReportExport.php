<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\ReportExport;
use App\Support\CourseAssessmentProgress;
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
            fputcsv($stream, ['ID Karyawan', 'Nama Karyawan', 'Kode Course', 'Course', 'Status', 'Nilai course modul', 'Status nilai course', 'Modul evaluatif selesai', 'Skor Pre-Test course', 'Skor Post-Test course terbaik', 'Peningkatan course', 'Percobaan Post-Test course', 'Tenggat', 'Selesai']);
            ReportEnrollmentQuery::make($export->filters)->reorder()->chunkById(500, function ($enrollments) use ($stream): void {
                $assessmentProgress = app(CourseAssessmentProgress::class)->for($enrollments);

                $enrollments->each(function (Enrollment $enrollment) use ($stream, $assessmentProgress): void {
                    $hasCourseAssessment = (int) $enrollment->course_assessment_count > 0;
                    $assessmentSummary = $assessmentProgress->get($enrollment->id)['summary'] ?? [
                        'score' => null,
                        'completed_modules' => 0,
                        'total_modules' => 0,
                        'is_final' => false,
                    ];
                    $improvement = ! $hasCourseAssessment
                        ? 'Tidak digunakan'
                        : ($enrollment->pre_test_score === null || $enrollment->best_post_score === null
                            ? null
                            : (float) $enrollment->best_post_score - (float) $enrollment->pre_test_score);

                    fputcsv($stream, array_map(fn (mixed $value) => $this->csvValue($value), [
                        $enrollment->user?->employee_id,
                        $enrollment->user?->name,
                        $enrollment->course?->code,
                        $enrollment->course?->title,
                        $enrollment->status,
                        $assessmentSummary['total_modules'] === 0 ? 'Tidak digunakan' : $assessmentSummary['score'],
                        $assessmentSummary['total_modules'] === 0 ? 'Tidak digunakan' : ($assessmentSummary['is_final'] ? 'Nilai akhir' : 'Rata-rata berjalan'),
                        $assessmentSummary['total_modules'] === 0 ? 'Tidak digunakan' : "{$assessmentSummary['completed_modules']} dari {$assessmentSummary['total_modules']}",
                        $hasCourseAssessment ? $enrollment->pre_test_score : 'Tidak digunakan',
                        $hasCourseAssessment ? $enrollment->best_post_score : 'Tidak digunakan',
                        $improvement,
                        $hasCourseAssessment ? $enrollment->post_test_attempts : 'Tidak digunakan',
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
