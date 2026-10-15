<?php

namespace App\Services;

use App\Jobs\SendWhatsappNotification;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\UserNotification;

class UserNotificationService
{
    public function courseAssigned(User $user, Enrollment $enrollment): void
    {
        $enrollment->loadMissing('course:id,title');
        $this->send(
            $user,
            'Course baru ditetapkan',
            "Course {$enrollment->course?->title} telah ditetapkan untuk Anda.",
            "course-assigned:{$enrollment->id}",
        );
    }

    public function deadlineReminder(Enrollment $enrollment, int $daysRemaining): void
    {
        $enrollment->loadMissing(['user:id,name', 'course:id,title']);
        $dueDate = $enrollment->due_at?->toDateString();
        if (! $enrollment->user || ! $dueDate) {
            return;
        }

        $this->send(
            $enrollment->user,
            $daysRemaining === 0 ? 'Deadline pembelajaran hari ini' : 'Deadline pembelajaran 3 hari lagi',
            "Selesaikan {$enrollment->course?->title} paling lambat {$dueDate}.",
            "deadline:{$enrollment->id}:{$enrollment->due_at->format('Ymd')}:{$daysRemaining}",
        );
    }

    private function send(User $user, string $title, string $body, string $dedupeKey): void
    {
        $notification = UserNotification::query()->firstOrCreate(
            ['user_id' => $user->id, 'dedupe_key' => $dedupeKey],
            ['title' => $title, 'body' => $body, 'whatsapp_status' => 'queued'],
        );

        if ($notification->wasRecentlyCreated) {
            SendWhatsappNotification::dispatch($notification->id)->afterCommit();
        }
    }
}
