<?php

namespace App\Http\Controllers;

use App\Jobs\IssueCertificate;
use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentRetakeAuthorization;
use App\Models\Course;
use App\Models\Enrollment;
use App\Support\ModuleLearningProgression;
use App\Support\ReportEnrollmentQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TrainerProgressController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $hasCentralAccess = $user->hasPermission('manage-enrollments') || $user->hasPermission('view-reports');
        $isAssignedTrainer = $user->hasRole('trainer') && $user->hasPermission('manage-courses');
        abort_unless($hasCentralAccess || $isAssignedTrainer, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'pending_review', 'completed', 'not_passed'])],
            'course_id' => ['nullable', 'integer'],
        ]);
        $courses = ($hasCentralAccess ? Course::query() : $user->managedCourses())
            ->orderBy('title')
            ->get(['courses.id', 'courses.code', 'courses.title']);
        $courseIds = $courses->pluck('id');
        $filters = [
            'search' => trim($filters['search'] ?? ''),
            'status' => $filters['status'] ?? '',
            'course_id' => isset($filters['course_id']) ? (int) $filters['course_id'] : null,
        ];

        abort_if($filters['course_id'] !== null && ! $courseIds->contains($filters['course_id']), 404);

        $enrollments = ReportEnrollmentQuery::make($filters)
            ->whereIn('course_id', $courseIds)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
        $moduleAssessments = $this->moduleAssessments($enrollments->getCollection());
        $retakeAssessments = $this->retakeAssessments($enrollments->getCollection());

        $enrollments->through(fn (Enrollment $enrollment) => [
            'id' => $enrollment->id,
            'status' => $enrollment->status,
            'due_at' => $enrollment->due_at?->toDateString(),
            'completed_at' => $enrollment->completed_at?->toDateString(),
            'pre_test_score' => $enrollment->pre_test_score === null ? null : (float) $enrollment->pre_test_score,
            'best_post_score' => $enrollment->best_post_score === null ? null : (float) $enrollment->best_post_score,
            'improvement' => $enrollment->pre_test_score === null || $enrollment->best_post_score === null ? null : round((float) $enrollment->best_post_score - (float) $enrollment->pre_test_score, 2),
            'post_test_attempts' => (int) $enrollment->post_test_attempts,
            'user' => $enrollment->user?->only(['employee_id', 'name']),
            'course' => $enrollment->course?->only(['code', 'title']),
            'module_assessments' => $moduleAssessments->get($enrollment->id, []),
            'retake_assessments' => $retakeAssessments->get($enrollment->id, []),
            'can_approve' => $enrollment->status === 'pending_review',
        ]);

        return Inertia::render('trainer-progress/Index', [
            'enrollments' => $enrollments,
            'courses' => $courses,
            'filters' => $filters,
        ]);
    }

    public function approve(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $user = $request->user();
        $hasCentralAccess = $user->hasPermission('manage-enrollments') || $user->hasPermission('view-reports');
        $isAssignedTrainer = $user->hasRole('trainer') && $user->hasPermission('manage-courses')
            && $user->managedCourses()->whereKey($enrollment->course_id)->exists();
        abort_unless($hasCentralAccess || $isAssignedTrainer, 403);
        $enrollment = DB::transaction(function () use ($enrollment): Enrollment {
            $enrollment = Enrollment::query()
                ->with('course')
                ->whereKey($enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($enrollment->status === 'pending_review', 422, 'Course belum siap untuk evaluasi akhir.');
            abort_unless(app(ModuleLearningProgression::class)->courseReadyForReview($enrollment->course, $enrollment), 422, 'Tahapan course belum lengkap.');

            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);

            return $enrollment;
        });

        ActivityLog::record($user, 'enrollment.final_review_approved', $enrollment, [
            'course_id' => $enrollment->course_id,
        ]);

        if ($enrollment->course->certificate_enabled) {
            IssueCertificate::dispatch($enrollment->id)->afterCommit();
        }

        return back()->with('success', 'Kelulusan course berhasil disetujui.');
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return Collection<int, array<int, array{module: array{sequence: int<0, max>|null, title: string|null}, pre_test: array{score: float}|null, post_test: array{best_score: float, attempts: int, passed: bool}|null}>>
     */
    private function moduleAssessments(Collection $enrollments): Collection
    {
        if ($enrollments->isEmpty()) {
            return collect();
        }

        $assessments = Assessment::query()
            ->whereIn('course_id', $enrollments->pluck('course_id')->unique())
            ->whereNotNull('module_id')
            ->with('module:id,course_id,title,sequence')
            ->get(['id', 'course_id', 'module_id', 'type']);
        $attempts = AssessmentAttempt::query()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->whereNotNull('submitted_at')
            ->orderBy('attempt_number')
            ->get(['assessment_id', 'enrollment_id', 'attempt_number', 'score', 'passed'])
            ->groupBy(fn (AssessmentAttempt $attempt) => "{$attempt->enrollment_id}:{$attempt->assessment_id}");

        return $enrollments->mapWithKeys(function (Enrollment $enrollment) use ($assessments, $attempts): array {
            $modules = $assessments
                ->where('course_id', $enrollment->course_id)
                ->groupBy('module_id')
                ->sortBy(fn (Collection $assessments) => $assessments->first()->module?->sequence)
                ->map(function (Collection $assessments) use ($attempts, $enrollment): array {
                    $preTest = $assessments->firstWhere('type', 'pre_test');
                    $postTest = $assessments->firstWhere('type', 'post_test');
                    $module = $assessments->first()->module;

                    return [
                        'module' => ['sequence' => $module?->sequence, 'title' => $module?->title],
                        'pre_test' => $preTest === null ? null : $this->preTestSummary($attempts->get("{$enrollment->id}:{$preTest->id}", collect())),
                        'post_test' => $postTest === null ? null : $this->postTestSummary($attempts->get("{$enrollment->id}:{$postTest->id}", collect())),
                    ];
                })
                ->values()
                ->all();

            return [$enrollment->id => $modules];
        });
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return Collection<int, array<int, array{id: int, label: non-falsy-string, status: 'available'|'pending'|'exhausted'}>>
     */
    private function retakeAssessments(Collection $enrollments): Collection
    {
        if ($enrollments->isEmpty()) {
            return collect();
        }

        $assessments = Assessment::query()
            ->whereIn('course_id', $enrollments->pluck('course_id')->unique())
            ->where('type', 'post_test')
            ->with('module:id,title,sequence')
            ->get(['id', 'course_id', 'module_id', 'attempt_limit']);
        $assessmentIds = $assessments->pluck('id');
        $attempts = AssessmentAttempt::query()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereIn('assessment_id', $assessmentIds)
            ->whereNotNull('submitted_at')
            ->orderBy('attempt_number')
            ->get(['assessment_id', 'enrollment_id', 'attempt_number', 'passed'])
            ->groupBy(fn (AssessmentAttempt $attempt) => "{$attempt->enrollment_id}:{$attempt->assessment_id}");
        $activeAttemptKeys = AssessmentAttempt::query()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereIn('assessment_id', $assessmentIds)
            ->whereNull('submitted_at')
            ->get(['assessment_id', 'enrollment_id'])
            ->mapWithKeys(fn (AssessmentAttempt $attempt) => ["{$attempt->enrollment_id}:{$attempt->assessment_id}" => true]);
        $pendingAuthorizationKeys = AssessmentRetakeAuthorization::query()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereIn('assessment_id', $assessmentIds)
            ->whereNull('used_at')
            ->whereNull('cancelled_at')
            ->get(['assessment_id', 'enrollment_id'])
            ->mapWithKeys(fn (AssessmentRetakeAuthorization $authorization) => ["{$authorization->enrollment_id}:{$authorization->assessment_id}" => true]);

        return $enrollments->mapWithKeys(function (Enrollment $enrollment) use ($assessments, $attempts, $activeAttemptKeys, $pendingAuthorizationKeys): array {
            if ($enrollment->status !== 'pending_review') {
                return [$enrollment->id => []];
            }

            $retakes = $assessments
                ->where('course_id', $enrollment->course_id)
                ->map(function (Assessment $assessment) use ($attempts, $enrollment, $activeAttemptKeys, $pendingAuthorizationKeys): ?array {
                    $key = "{$enrollment->id}:{$assessment->id}";
                    $assessmentAttempts = $attempts->get($key, collect());
                    $latest = $assessmentAttempts->last();

                    if ($latest === null || $latest->passed === true) {
                        return null;
                    }

                    $status = $assessmentAttempts->count() >= $assessment->attempt_limit
                        ? 'exhausted'
                        : ($activeAttemptKeys->has($key) || $pendingAuthorizationKeys->has($key) ? 'pending' : 'available');

                    return [
                        'id' => $assessment->id,
                        'label' => $assessment->module_id === null
                            ? 'Post-Test course'
                            : 'Post-Test modul '.$assessment->module->title,
                        'status' => $status,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            return [$enrollment->id => $retakes];
        });
    }

    /**
     * @param  Collection<int, AssessmentAttempt>  $attempts
     * @return array{score: float}|null
     */
    private function preTestSummary(Collection $attempts): ?array
    {
        $attempt = $attempts->first();

        return $attempt === null ? null : ['score' => (float) $attempt->score];
    }

    /**
     * @param  Collection<int, AssessmentAttempt>  $attempts
     * @return array{best_score: float, attempts: int, passed: bool}|null
     */
    private function postTestSummary(Collection $attempts): ?array
    {
        if ($attempts->isEmpty()) {
            return null;
        }

        return [
            'best_score' => (float) $attempts->max('score'),
            'attempts' => $attempts->count(),
            'passed' => $attempts->contains('passed', true),
        ];
    }
}
