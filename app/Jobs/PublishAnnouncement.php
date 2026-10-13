<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $announcementId) {}

    public function handle(): void
    {
        $announcement = Announcement::query()->findOrFail($this->announcementId);

        User::query()->where('status', 'active')->select('id')->orderBy('id')->chunkById(200, function ($users) use ($announcement): void {
            $users->each(function (User $user) use ($announcement): void {
                $notification = UserNotification::query()->firstOrCreate(
                    ['announcement_id' => $announcement->id, 'user_id' => $user->id],
                    ['title' => $announcement->title, 'body' => $announcement->body, 'whatsapp_status' => 'queued'],
                );

                if ($notification->wasRecentlyCreated) {
                    SendWhatsappNotification::dispatch($notification->id);
                }
            });
        });

        $announcement->update(['published_at' => now()]);
    }
}
