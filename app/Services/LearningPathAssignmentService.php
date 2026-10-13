<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\LearningPath;
use App\Models\LearningPathAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearningPathAssignmentService
{
    public function assign(User $user, LearningPath $learningPath): void
    {
        DB::transaction(function () use ($user, $learningPath): void {
            LearningPathAssignment::query()->firstOrCreate([
                'user_id' => $user->id,
                'learning_path_id' => $learningPath->id,
            ]);

            $learningPath->pathCourses()
                ->whereHas('course', fn ($query) => $query->where('status', 'published'))
                ->pluck('course_id')
                ->each(fn (int $courseId) => Enrollment::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                ], ['status' => 'active']));
        });
    }

    public function assignMatching(User $user): void
    {
        if ($user->status !== 'active' || ! $user->division_id || ! $user->position_id || ! $user->branch_id) {
            return;
        }

        LearningPath::query()
            ->where('status', 'published')
            ->whereHas('rules', fn ($query) => $query
                ->where('division_id', $user->division_id)
                ->where('position_id', $user->position_id)
                ->where('branch_id', $user->branch_id))
            ->get()
            ->each(fn (LearningPath $learningPath) => $this->assign($user, $learningPath));
    }
}
