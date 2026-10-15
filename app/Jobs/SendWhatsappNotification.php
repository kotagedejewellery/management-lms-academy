<?php

namespace App\Jobs;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class SendWhatsappNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function __construct(public int $notificationId) {}

    public function handle(): void
    {
        $notification = UserNotification::query()->with('user:id,name,phone')->findOrFail($this->notificationId);
        $token = config('services.fonnte.token');

        if (blank($notification->user?->phone) || blank($token)) {
            $notification->update(['whatsapp_status' => 'skipped', 'whatsapp_error' => 'Nomor WhatsApp atau konfigurasi Fonnte belum tersedia.']);

            return;
        }

        $response = Http::asForm()->withHeaders(['Authorization' => $token])->timeout(20)->post(config('services.fonnte.url'), [
            'target' => $notification->user->phone,
            'message' => "{$notification->title}\n\n{$notification->body}",
        ]);

        if ($response->successful() && $response->json('status')) {
            $notification->update(['whatsapp_status' => 'sent', 'whatsapp_error' => null]);

            return;
        }

        if ($response->status() === 429 || $response->serverError()) {
            throw new RuntimeException('Layanan WhatsApp sementara tidak tersedia.');
        }

        $notification->update(['whatsapp_status' => 'failed', 'whatsapp_error' => 'Pesan WhatsApp tidak dapat dikirim.']);
    }

    public function failed(Throwable $exception): void
    {
        UserNotification::query()->whereKey($this->notificationId)->update(['whatsapp_status' => 'failed', 'whatsapp_error' => 'Pesan WhatsApp tidak dapat dikirim.']);
    }
}
