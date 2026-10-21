<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentRetakeAuthorization;
use App\Models\Enrollment;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AssessmentRetakeAuthorizationController extends Controller
{
    public function store(Request $request, Enrollment $enrollment, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->course_id === $enrollment->course_id && $assessment->type === 'post_test', 404);
        $this->authorizeAuthorization($request, $enrollment);

        $authorization = DB::transaction(function () use ($assessment, $enrollment, $request): AssessmentRetakeAuthorization {
            $enrollment = Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $attempts = $assessment->attempts()
                ->where('enrollment_id', $enrollment->id)
                ->whereNotNull('submitted_at')
                ->lockForUpdate()
                ->get();
            $latest = $attempts->sortByDesc('attempt_number')->first();

            if ($latest === null || $latest->passed === true) {
                throw ValidationException::withMessages(['assessment' => 'Kesempatan ulang hanya dapat dibuka setelah Post-Test belum memenuhi standar.']);
            }
            if ($attempts->count() >= $assessment->attempt_limit) {
                throw ValidationException::withMessages(['assessment' => 'Batas percobaan Post-Test telah tercapai.']);
            }
            if ($assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNull('submitted_at')->exists()) {
                throw ValidationException::withMessages(['assessment' => 'Peserta masih memiliki Post-Test yang sedang dikerjakan.']);
            }
            if (AssessmentRetakeAuthorization::query()
                ->where('assessment_id', $assessment->id)
                ->where('enrollment_id', $enrollment->id)
                ->whereNull('used_at')
                ->whereNull('cancelled_at')
                ->lockForUpdate()
                ->exists()) {
                throw ValidationException::withMessages(['assessment' => 'Kesempatan ulang Post-Test sudah dibuka dan menunggu peserta.']);
            }

            $authorization = AssessmentRetakeAuthorization::query()->create([
                'assessment_id' => $assessment->id,
                'enrollment_id' => $enrollment->id,
                'authorized_by' => $request->user()->id,
            ]);
            $enrollment->update(['status' => 'active', 'completed_at' => null]);

            return $authorization;
        });

        ActivityLog::record($request->user(), 'assessment.retake_authorized', $assessment, [
            'enrollment_id' => $enrollment->id,
        ]);
        app(UserNotificationService::class)->assessmentRetakeAuthorized($enrollment, $assessment, $authorization);
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kesempatan ulang Post-Test berhasil dibuka.',
        ]);

        return back();
    }

    public function destroy(Request $request, Enrollment $enrollment, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->course_id === $enrollment->course_id && $assessment->type === 'post_test', 404);
        $this->authorizeAuthorization($request, $enrollment);

        DB::transaction(function () use ($assessment, $enrollment): void {
            $authorization = AssessmentRetakeAuthorization::query()
                ->where('assessment_id', $assessment->id)
                ->where('enrollment_id', $enrollment->id)
                ->whereNull('used_at')
                ->whereNull('cancelled_at')
                ->oldest('id')
                ->lockForUpdate()
                ->first();
            if (! $authorization) {
                throw ValidationException::withMessages(['assessment' => 'Tidak ada kesempatan ulang yang dapat dibatalkan.']);
            }

            $authorization->update(['cancelled_at' => now()]);
        });

        ActivityLog::record($request->user(), 'assessment.retake_cancelled', $assessment, [
            'enrollment_id' => $enrollment->id,
        ]);
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kesempatan ulang Post-Test dibatalkan.',
        ]);

        return back();
    }

    private function authorizeAuthorization(Request $request, Enrollment $enrollment): void
    {
        $user = $request->user();
        $canManageAll = $user->hasPermission('manage-enrollments') || $user->hasPermission('view-reports');

        abort_unless($canManageAll || ($user->hasRole('trainer') && $user->canManageCourse($enrollment->course)), 403);
    }
}
