<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeCertificateAccess($user);
        $canManage = $user->hasPermission('manage-enrollments');

        $certificates = Certificate::query()
            ->with(['user:id,name,employee_id', 'course:id,code,title'])
            ->when(! $canManage, fn ($query) => $query->where('user_id', $user->id))
            ->whereNotNull('issued_at')
            ->latest('issued_at')
            ->paginate(15)
            ->through(fn (Certificate $certificate) => [
                'id' => $certificate->id,
                'number' => $certificate->number,
                'issued_at' => $certificate->issued_at?->toDateString(),
                'recipient' => $certificate->user?->only(['name', 'employee_id']),
                'course' => $certificate->course?->only(['code', 'title']),
                'download_url' => $certificate->path ? route('certificates.download', $certificate) : null,
            ]);

        return Inertia::render('certificates/Index', [
            'certificates' => $certificates,
            'can_manage_certificates' => $canManage,
        ]);
    }

    public function download(Request $request, Certificate $certificate): StreamedResponse
    {
        $user = $request->user();
        $this->authorizeCertificateAccess($user);
        abort_unless($certificate->user_id === $user->id || $user->hasPermission('manage-enrollments'), 403);
        abort_unless($certificate->path && Storage::disk()->exists($certificate->path), 404);

        ActivityLog::record($user, 'certificate.downloaded', $certificate, ['number' => $certificate->number]);

        return Storage::disk()->download($certificate->path, "sertifikat-{$certificate->number}.pdf", ['Content-Type' => 'application/pdf']);
    }

    private function authorizeCertificateAccess(User $user): void
    {
        abort_if($user->hasRole('trainer') && ! $user->hasPermission('manage-enrollments'), 403);
    }
}
