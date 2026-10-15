<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Services\UserNotificationService;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'learning:send-deadline-reminders';

    protected $description = 'Send in-app and WhatsApp reminders for active course deadlines.';

    public function handle(UserNotificationService $notifications): int
    {
        $today = today()->startOfDay();

        Enrollment::query()
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->whereNotNull('due_at')
            ->whereDate('due_at', '>=', $today)
            ->whereDate('due_at', '<=', $today->copy()->addDays(3))
            ->with(['user:id,name', 'course:id,title'])
            ->chunkById(200, function ($enrollments) use ($notifications, $today): void {
                foreach ($enrollments as $enrollment) {
                    $daysRemaining = (int) $today->diffInDays($enrollment->due_at->startOfDay());
                    if (in_array($daysRemaining, [0, 3], true)) {
                        $notifications->deadlineReminder($enrollment, $daysRemaining);
                    }
                }
            });

        return self::SUCCESS;
    }
}
