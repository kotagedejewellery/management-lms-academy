<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Enrollment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class IssueCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $enrollmentId) {}

    public function handle(): void
    {
        $enrollment = Enrollment::query()
            ->with(['user:id,name,employee_id', 'course:id,code,title,certificate_enabled'])
            ->find($this->enrollmentId);

        if (! $enrollment || $enrollment->status !== 'completed' || ! $enrollment->course->certificate_enabled) {
            return;
        }

        $certificate = Certificate::query()->firstOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'number' => sprintf('KGJ-%s-%06d', now()->format('Y'), $enrollment->id),
            ],
        );

        if ($certificate->path && Storage::disk()->exists($certificate->path)) {
            return;
        }

        $certificate->loadMissing('user:id,name,employee_id', 'course:id,code,title');
        $pdf = new Dompdf(new Options(['isRemoteEnabled' => false]));
        $pdf->loadHtml(view('certificates.pdf', ['certificate' => $certificate])->render());
        $pdf->setPaper('a4', 'landscape');
        $pdf->render();

        $path = "certificates/{$certificate->number}.pdf";
        throw_unless(Storage::disk()->put($path, $pdf->output()), RuntimeException::class, 'Tidak dapat menyimpan PDF sertifikat.');
        $certificate->update(['path' => $path, 'issued_at' => now()]);

        if ($certificate->wasRecentlyCreated) {
            ActivityLog::record($enrollment->user, 'certificate.issued', $certificate, ['number' => $certificate->number]);
        }
    }
}
