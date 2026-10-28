<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;

class ReportEnrollmentQuery
{
    /**
     * @param  array{search?: string, status?: string, course_id?: int|null, division_id?: int|null}  $filters
     * @return Builder<Enrollment>
     */
    public static function make(array $filters): Builder
    {
        $bestPostScore = AssessmentAttempt::query()
            ->selectRaw('max(assessment_attempts.score)')
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->whereColumn('assessment_attempts.enrollment_id', 'enrollments.id')
            ->where('assessments.type', 'post_test')
            ->whereNull('assessments.module_id')
            ->whereNotNull('assessment_attempts.submitted_at');
        $firstPreScore = AssessmentAttempt::query()
            ->select('assessment_attempts.score')
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->whereColumn('assessment_attempts.enrollment_id', 'enrollments.id')
            ->where('assessments.type', 'pre_test')
            ->whereNull('assessments.module_id')
            ->whereNotNull('assessment_attempts.submitted_at')
            ->orderBy('assessment_attempts.attempt_number')
            ->limit(1);
        $postTestAttempts = AssessmentAttempt::query()
            ->selectRaw('count(*)')
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->whereColumn('assessment_attempts.enrollment_id', 'enrollments.id')
            ->where('assessments.type', 'post_test')
            ->whereNull('assessments.module_id')
            ->whereNotNull('assessment_attempts.submitted_at');
        $courseAssessmentCount = Assessment::query()
            ->selectRaw('count(*)')
            ->whereColumn('assessments.course_id', 'enrollments.course_id')
            ->whereNull('assessments.module_id');

        return Enrollment::query()
            ->select(['id', 'user_id', 'course_id', 'status', 'due_at', 'completed_at'])
            ->selectSub($bestPostScore, 'best_post_score')
            ->selectSub($firstPreScore, 'pre_test_score')
            ->selectSub($postTestAttempts, 'post_test_attempts')
            ->selectSub($courseAssessmentCount, 'course_assessment_count')
            ->with(['user:id,employee_id,name', 'course:id,code,title'])
            ->when(in_array($filters['status'] ?? '', ['active', 'pending_review', 'completed', 'not_passed'], true), fn (Builder $query) => $query->where('status', $filters['status'] ?? ''))
            ->when($filters['course_id'] ?? null, fn (Builder $query) => $query->where('course_id', $filters['course_id'] ?? null))
            ->when($filters['division_id'] ?? null, fn (Builder $query) => $query->whereHas('user', fn (Builder $query) => $query->where('division_id', $filters['division_id'] ?? null)))
            ->when(($filters['search'] ?? '') !== '', function (Builder $query) use ($filters): void {
                $term = '%'.mb_strtolower($filters['search'] ?? '').'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->whereHas('user', fn (Builder $query) => $query->whereRaw('lower(name) like ?', [$term])->orWhereRaw('lower(coalesce(employee_id, \'\')) like ?', [$term]))
                        ->orWhereHas('course', fn (Builder $query) => $query->whereRaw('lower(title) like ?', [$term])->orWhereRaw('lower(code) like ?', [$term]));
                });
            });
    }
}
