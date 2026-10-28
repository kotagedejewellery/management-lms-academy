<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class CourseAssessmentProgress
{
    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return Collection<int, array{modules: array<int, array{module: array{sequence: int|null, title: string|null}, pre_test: array{score: float}|null, post_test: array{best_score: float, attempts: int, passed: bool}|null}>, summary: array{score: float|null, completed_modules: int, total_modules: int, is_final: bool}}>
     */
    public function for(Collection $enrollments): Collection
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
            $scoreableModules = 0;
            $completedScores = collect();
            $modules = $assessments
                ->where('course_id', $enrollment->course_id)
                ->groupBy('module_id')
                ->sortBy(fn (Collection $assessments) => $assessments->first()->module?->sequence)
                ->map(function (Collection $assessments) use ($attempts, $enrollment, &$scoreableModules, $completedScores): array {
                    $preTest = $assessments->firstWhere('type', 'pre_test');
                    $postTest = $assessments->firstWhere('type', 'post_test');
                    $module = $assessments->first()->module;
                    $preTestSummary = $preTest === null ? null : $this->preTestSummary($attempts->get("{$enrollment->id}:{$preTest->id}", collect()));
                    $postTestSummary = $postTest === null ? null : $this->postTestSummary($attempts->get("{$enrollment->id}:{$postTest->id}", collect()));

                    if ($preTest !== null && $postTest !== null) {
                        $scoreableModules++;

                        if ($preTestSummary !== null && $postTestSummary !== null) {
                            $completedScores->push(($preTestSummary['score'] + $postTestSummary['best_score']) / 2);
                        }
                    }

                    return [
                        'module' => ['sequence' => $module?->sequence, 'title' => $module?->title],
                        'pre_test' => $preTestSummary,
                        'post_test' => $postTestSummary,
                    ];
                })
                ->values()
                ->all();
            $completedModules = $completedScores->count();

            return [$enrollment->id => [
                'modules' => $modules,
                'summary' => [
                    'score' => $completedModules === 0 ? null : round($completedScores->avg(), 2),
                    'completed_modules' => $completedModules,
                    'total_modules' => $scoreableModules,
                    'is_final' => $scoreableModules > 0 && $completedModules === $scoreableModules,
                ],
            ]];
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
