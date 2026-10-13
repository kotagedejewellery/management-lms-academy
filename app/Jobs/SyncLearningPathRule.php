<?php

namespace App\Jobs;

use App\Models\LearningPathRule;
use App\Models\User;
use App\Services\LearningPathAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncLearningPathRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $ruleId) {}

    public function handle(LearningPathAssignmentService $assignments): void
    {
        $rule = LearningPathRule::query()->with('learningPath')->find($this->ruleId);
        if (! $rule || $rule->learningPath?->status !== 'published') {
            return;
        }

        User::query()
            ->where('status', 'active')
            ->where('division_id', $rule->division_id)
            ->where('position_id', $rule->position_id)
            ->where('branch_id', $rule->branch_id)
            ->orderBy('id')
            ->chunkById(200, fn ($users) => $users->each(fn (User $user) => $assignments->assign($user, $rule->learningPath)));
    }
}
