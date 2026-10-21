<?php

namespace App\Http\Controllers;

use App\Jobs\IssueCertificate;
use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentDraft;
use App\Models\AssessmentRetakeAuthorization;
use App\Models\Course;
use App\Models\Enrollment;
use App\Support\ModuleLearningProgression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentAttemptController extends Controller
{
    public function show(Request $request, Course $course, Assessment $assessment): Response
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        $activeAttempt = $assessment->attempts()
            ->where('enrollment_id', $enrollment->id)
            ->whereNull('submitted_at')
            ->first();
        if ($activeAttempt?->expires_at?->isPast()) {
            $this->expire($assessment, $activeAttempt);
            $activeAttempt = null;
        }
        if (! $activeAttempt && $this->requiresExplicitRetakeStart($assessment, $enrollment)) {
            $this->ensureAvailable($assessment, $course, $enrollment);

            return Inertia::render('learning/AssessmentStart', [
                'course' => $course->only(['id', 'title']),
                'assessment' => $assessment->only(['id', 'type', 'time_limit_minutes']),
            ]);
        }
        $activeAttempt ??= $this->beginAttempt($assessment, $course, $enrollment);
        $assessment->load('questions.answers');
        $draft = AssessmentDraft::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $enrollment->id)
            ->value('answers') ?? [];

        return Inertia::render('learning/Assessment', [
            'course' => $course->only(['id', 'title']),
            'assessment' => [
                ...$assessment->only(['id', 'type', 'time_limit_minutes']),
                'expires_at' => $activeAttempt->expires_at?->toIso8601String(),
                'draft_answers' => $draft,
                'questions' => $assessment->questions->map(fn ($question) => [...$question->only(['id', 'prompt', 'points', 'sequence']), 'answers' => $question->answers->map->only(['id', 'content', 'sequence'])]),
            ],
        ]);
    }

    public function start(Request $request, Course $course, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        $this->beginAttempt($assessment, $course, $enrollment);

        return to_route('my-learning.assessments.show', [$course, $assessment]);
    }

    public function saveDraft(Request $request, Course $course, Assessment $assessment): HttpResponse
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        $assessment->load('questions.answers');
        $submittedAnswers = $request->validate(['answers' => ['present', 'array']])['answers'];

        $activeAttempt = $assessment->attempts()
            ->where('enrollment_id', $enrollment->id)
            ->whereNull('submitted_at')
            ->first();
        if ($activeAttempt?->expires_at?->isPast()) {
            $this->expire($assessment, $activeAttempt);
            throw ValidationException::withMessages(['assessment' => 'Waktu assessment telah habis.']);
        }
        $activeAttempt ??= $this->beginAttempt($assessment, $course, $enrollment);
        $this->ensureAvailable($assessment, $course, $enrollment, $activeAttempt);

        AssessmentDraft::query()->updateOrCreate(
            ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id],
            ['answers' => $this->validAnswers($assessment, $submittedAnswers)],
        );

        return response()->noContent();
    }

    public function submit(Request $request, Course $course, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        $submittedAnswers = $request->validate(['answers' => ['present', 'array']])['answers'];
        $assessment->load('questions.answers');
        $submittedAnswers = $this->validAnswers($assessment, $submittedAnswers);
        if (! $assessment->time_limit_minutes) {
            $this->ensureAvailable($assessment, $course, $enrollment);
        }

        $attempt = DB::transaction(function () use ($assessment, $course, $enrollment, $submittedAnswers) {
            $enrollment = Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $activeAttempt = $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNull('submitted_at')->lockForUpdate()->first();
            $draft = AssessmentDraft::query()
                ->where('assessment_id', $assessment->id)
                ->where('enrollment_id', $enrollment->id)
                ->lockForUpdate()
                ->first();
            $answers = array_replace(is_array($draft?->answers) ? $draft->answers : [], $submittedAnswers);
            $answers = $this->validAnswers($assessment, $answers);
            if ($activeAttempt?->expires_at?->isPast()) {
                $this->expire($assessment, $activeAttempt);

                return null;
            }
            $this->ensureAvailable($assessment, $course, $enrollment, $activeAttempt);
            $total = $assessment->questions->sum('points');
            abort_if($total === 0, 422, 'Assessment belum memiliki soal.');
            $earned = 0;
            $responses = [];
            foreach ($assessment->questions as $question) {
                $answer = $question->answers->firstWhere('id', (int) ($answers[$question->id] ?? 0));
                $correct = (bool) $answer?->is_correct;
                $earned += $correct ? $question->points : 0;
                $responses[] = ['question_id' => $question->id, 'answer_id' => $answer?->id, 'is_correct' => $correct];
            }
            $score = round(($earned / $total) * 100, 2);
            $passed = $assessment->type === 'post_test' ? $score >= $assessment->passing_grade : null;
            if (! $activeAttempt) {
                $this->consumeRetakeAuthorization($assessment, $enrollment);
            }
            $attempt = $activeAttempt ?? $assessment->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => $assessment->attempts()->where('enrollment_id', $enrollment->id)->count() + 1, 'started_at' => now()]);
            $attempt->update(['score' => $score, 'passed' => $passed, 'submitted_at' => now()]);
            $attempt->responses()->createMany($responses);
            $draft?->delete();
            if ($assessment->module_id === null && $passed === true && $course->requires_post_test && app(ModuleLearningProgression::class)->courseModulesCompleted($course, $enrollment)) {
                $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
            } elseif ($assessment->module_id !== null && $passed === true && ! $course->requires_post_test && app(ModuleLearningProgression::class)->courseModulesCompleted($course, $enrollment)) {
                $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
            } elseif ($assessment->type === 'post_test' && $attempt->attempt_number >= $assessment->attempt_limit) {
                $enrollment->update(['status' => 'not_passed', 'completed_at' => null]);
            }

            return $attempt;
        });

        if ($attempt) {
            ActivityLog::record($request->user(), 'assessment.submitted', $attempt, [
                'type' => $assessment->type,
                'score' => $attempt->score,
                'passed' => $attempt->passed,
            ]);
            if ($assessment->type === 'post_test' && $attempt->passed === true && $course->certificate_enabled && $enrollment->fresh()->status === 'completed') {
                IssueCertificate::dispatch($enrollment->id)->afterCommit();
            }
        }

        return to_route('my-learning.assessments.receipt', [$course, $assessment]);
    }

    public function receipt(Request $request, Course $course, Assessment $assessment): Response
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        abort_unless($assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists(), 404);

        return Inertia::render('learning/AssessmentResult', [
            'course' => $course->only(['id', 'title']),
            'assessment' => $assessment->only(['id', 'type']),
        ]);
    }

    public function result(Request $request, Course $course, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $this->enrollment($request, $course, true);

        return to_route('my-learning.show', $course);
    }

    private function enrollment(Request $request, Course $course, bool $includeTerminal = false): Enrollment
    {
        $statuses = $includeTerminal ? ['active', 'completed', 'not_passed'] : ['active', 'completed'];
        $enrollment = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->whereIn('status', $statuses)
            ->whereHas('course', fn ($query) => $query->where('status', 'published'))
            ->firstOrFail();
        abort_unless($course->prerequisiteCompletedBy($request->user()), 403);

        return $enrollment;
    }

    private function beginAttempt(Assessment $assessment, Course $course, Enrollment $enrollment): AssessmentAttempt
    {
        return DB::transaction(function () use ($assessment, $course, $enrollment) {
            $enrollment = Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $activeAttempt = $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNull('submitted_at')->lockForUpdate()->first();
            if ($activeAttempt?->expires_at?->isPast()) {
                $this->expire($assessment, $activeAttempt);
                $activeAttempt = null;
            }
            $this->ensureAvailable($assessment, $course, $enrollment, $activeAttempt);

            $enrollment->update(['started_at' => $enrollment->started_at ?? now()]);

            if (! $activeAttempt) {
                $this->consumeRetakeAuthorization($assessment, $enrollment);
            }

            return $activeAttempt ?? $assessment->attempts()->create([
                'enrollment_id' => $enrollment->id,
                'attempt_number' => $assessment->attempts()->where('enrollment_id', $enrollment->id)->count() + 1,
                'started_at' => now(),
                'expires_at' => $assessment->time_limit_minutes ? now()->addMinutes($assessment->time_limit_minutes) : null,
            ]);
        });
    }

    private function expire(Assessment $assessment, AssessmentAttempt $attempt): void
    {
        $attempt->update(['score' => 0, 'passed' => $assessment->type === 'post_test' ? false : null, 'submitted_at' => now()]);
        if ($assessment->type === 'post_test' && $attempt->attempt_number >= $assessment->attempt_limit) {
            Enrollment::query()->whereKey($attempt->enrollment_id)->update(['status' => 'not_passed', 'completed_at' => null]);
        }
        AssessmentDraft::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $attempt->enrollment_id)
            ->delete();
    }

    private function ensureAvailable(Assessment $assessment, Course $course, Enrollment $enrollment, ?AssessmentAttempt $activeAttempt = null): void
    {
        if ($enrollment->status !== 'active') {
            throw ValidationException::withMessages(['assessment' => 'Assessment telah ditutup. Management akan memberikan arahan bila diperlukan.']);
        }
        if (! $assessment->questions()->exists()) {
            throw ValidationException::withMessages(['assessment' => 'Assessment belum siap karena belum memiliki soal.']);
        }
        if (! app(ModuleLearningProgression::class)->canStartAssessment($assessment, $course, $enrollment)) {
            throw ValidationException::withMessages(['assessment' => $assessment->type === 'pre_test'
                ? 'Selesaikan modul sebelumnya sebelum membuka Pre-Test ini.'
                : 'Selesaikan seluruh lesson wajib sebelum membuka Post-Test.']);
        }
        $attemptLimit = $this->attemptLimit($assessment);
        $submittedAttemptCount = $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->count();
        if (! $activeAttempt && $submittedAttemptCount >= $attemptLimit) {
            throw ValidationException::withMessages(['assessment' => 'Assessment telah dikirim. Management akan memberikan arahan untuk tahap selanjutnya.']);
        }
        if (! $activeAttempt && $assessment->type === 'post_test' && $submittedAttemptCount > 0 && ! $this->hasAvailableRetakeAuthorization($assessment, $enrollment)) {
            throw ValidationException::withMessages(['assessment' => 'Assessment telah dikirim. Management akan memberikan arahan untuk tahap selanjutnya.']);
        }
    }

    private function consumeRetakeAuthorization(Assessment $assessment, Enrollment $enrollment): void
    {
        $submittedAttemptCount = $assessment->attempts()
            ->where('enrollment_id', $enrollment->id)
            ->whereNotNull('submitted_at')
            ->count();
        if ($assessment->type !== 'post_test' || $submittedAttemptCount === 0) {
            return;
        }

        $authorization = AssessmentRetakeAuthorization::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $enrollment->id)
            ->whereNull('used_at')
            ->whereNull('cancelled_at')
            ->oldest('id')
            ->lockForUpdate()
            ->first();
        if (! $authorization) {
            throw ValidationException::withMessages(['assessment' => 'Assessment telah dikirim. Management akan memberikan arahan untuk tahap selanjutnya.']);
        }

        $authorization->update(['used_at' => now()]);
    }

    private function hasAvailableRetakeAuthorization(Assessment $assessment, Enrollment $enrollment): bool
    {
        return AssessmentRetakeAuthorization::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $enrollment->id)
            ->whereNull('used_at')
            ->whereNull('cancelled_at')
            ->exists();
    }

    private function requiresExplicitRetakeStart(Assessment $assessment, Enrollment $enrollment): bool
    {
        return $assessment->type === 'post_test'
            && $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists()
            && $this->hasAvailableRetakeAuthorization($assessment, $enrollment);
    }

    private function attemptLimit(Assessment $assessment): int
    {
        return $assessment->type === 'pre_test' ? 1 : $assessment->attempt_limit;
    }

    /**
     * @param  array<array-key, mixed>  $answers
     * @return array<int, int>
     */
    private function validAnswers(Assessment $assessment, array $answers): array
    {
        $validAnswers = [];

        foreach ($answers as $questionId => $answerId) {
            if ($answerId === null || $answerId === '') {
                continue;
            }

            $question = $assessment->questions->firstWhere('id', (int) $questionId);
            if (! $question || ! is_scalar($answerId) || ! $question->answers->contains('id', (int) $answerId)) {
                throw ValidationException::withMessages(['answers' => 'Jawaban assessment tidak valid.']);
            }

            $validAnswers[$question->id] = (int) $answerId;
        }

        return $validAnswers;
    }
}
