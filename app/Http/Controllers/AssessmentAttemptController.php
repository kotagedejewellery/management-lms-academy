<?php

namespace App\Http\Controllers;

use App\Jobs\IssueCertificate;
use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentDraft;
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
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
        $activeAttempt = $this->beginAttempt($assessment, $course, $enrollment);
        $assessment->load('questions.answers');
        $attempts = $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->get();
        $draft = AssessmentDraft::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $enrollment->id)
            ->value('answers') ?? [];

        return Inertia::render('learning/Assessment', [
            'course' => $course->only(['id', 'title']),
            'assessment' => [
                ...$assessment->only(['id', 'type', 'time_limit_minutes']),
                'attempt_limit' => $this->attemptLimit($assessment),
                'attempts_used' => $attempts->count(),
                'best_score' => $attempts->max('score'),
                'attempt_number' => $activeAttempt->attempt_number,
                'expires_at' => $activeAttempt->expires_at?->toIso8601String(),
                'draft_answers' => $draft,
                'questions' => $assessment->questions->map(fn ($question) => [...$question->only(['id', 'prompt', 'points', 'sequence']), 'answers' => $question->answers->map->only(['id', 'content', 'sequence'])]),
            ],
        ]);
    }

    public function saveDraft(Request $request, Course $course, Assessment $assessment): HttpResponse
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
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
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
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

        return to_route('my-learning.assessments.result', [$course, $assessment]);
    }

    public function result(Request $request, Course $course, Assessment $assessment): Response
    {
        abort_unless($assessment->course_id === $course->id, 404);
        $enrollment = $this->enrollment($request, $course, true);
        $attempts = $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->orderByDesc('attempt_number')->get();
        abort_if($attempts->isEmpty(), 404);

        $baseline = $course->assessments()
            ->where('type', 'pre_test')
            ->when($assessment->module_id, fn ($query, int $moduleId) => $query->where('module_id', $moduleId), fn ($query) => $query->whereNull('module_id'))
            ->first()?->attempts()
            ->where('enrollment_id', $enrollment->id)
            ->whereNotNull('submitted_at')
            ->oldest('attempt_number')
            ->value('score');
        $bestScore = $attempts->max('score');
        $latest = $attempts->first();
        $latest->load(['responses.question.answers', 'responses.answer']);

        return Inertia::render('learning/AssessmentResult', [
            'course' => $course->only(['id', 'title']),
            'enrollment_status' => $enrollment->status,
            'assessment' => [
                ...$assessment->only(['id', 'type', 'passing_grade']),
                'attempt_limit' => $this->attemptLimit($assessment),
            ],
            'latest' => [...$latest->only(['attempt_number', 'score', 'passed', 'submitted_at']), 'duration_seconds' => $latest->started_at ? (int) $latest->started_at->diffInSeconds($latest->submitted_at) : null],
            'best_score' => $bestScore,
            'attempts_used' => $attempts->count(),
            'pre_test_score' => $baseline,
            'improvement' => $assessment->type === 'post_test' && $baseline !== null ? round((float) $bestScore - (float) $baseline, 2) : null,
            'review' => $latest->responses->sortBy(fn ($response) => $response->question->sequence)->map(fn ($response) => [
                'question' => $response->question->prompt,
                'selected_answer' => $response->answer?->content,
                'correct_answer' => $response->question->answers->firstWhere('is_correct', true)?->content,
                'is_correct' => $response->is_correct,
                'explanation' => $response->question->explanation,
            ])->values(),
        ]);
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
        if (! $assessment->questions()->exists()) {
            throw ValidationException::withMessages(['assessment' => 'Assessment belum siap karena belum memiliki soal.']);
        }
        if (! app(ModuleLearningProgression::class)->canStartAssessment($assessment, $course, $enrollment)) {
            throw ValidationException::withMessages(['assessment' => $assessment->type === 'pre_test'
                ? 'Selesaikan modul sebelumnya sebelum membuka Pre-Test ini.'
                : 'Selesaikan seluruh lesson wajib sebelum membuka Post-Test.']);
        }
        $attemptLimit = $this->attemptLimit($assessment);
        if (! $activeAttempt && $assessment->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->count() >= $attemptLimit) {
            throw ValidationException::withMessages(['assessment' => "Batas maksimal {$attemptLimit} percobaan telah tercapai."]);
        }
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
