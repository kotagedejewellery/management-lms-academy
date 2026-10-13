<?php

namespace App\Jobs;

use App\Models\LearningPath;
use App\Models\LearningPathAssignment;
use App\Models\User;
use App\Services\LearningPathAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncLearningPath implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $learningPathId) {}

    public function handle(LearningPathAssignmentService $assignments): void
    {
        $learningPath = LearningPath::query()->where('status', 'published')->find($this->learningPathId);

        if (! $learningPath) {
            return;
        }

        LearningPathAssignment::query()
            ->where('learning_path_id', $learningPath->id)
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunkById(200, function ($pathAssignments) use ($assignments, $learningPath): void {
                $users = User::query()
                    ->where('status', 'active')
                    ->whereIn('id', $pathAssignments->pluck('user_id'))
                    ->get();

                $users->each(fn (User $user) => $assignments->assign($user, $learningPath));
            });
    }
}
