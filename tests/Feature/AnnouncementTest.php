<?php

namespace Tests\Feature;

use App\Jobs\PublishAnnouncement;
use App\Jobs\SendWhatsappNotification;
use App\Models\Announcement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_queues_an_announcement_for_active_employees_only(): void
    {
        Queue::fake();
        $admin = $this->userWithPermission('manage-enrollments');
        $this->actingAs($admin)->post(route('announcements.store'), ['title' => 'Jadwal baru', 'body' => 'Silakan cek jadwal pembelajaran.'])->assertRedirect(route('announcements.index'));
        $announcement = Announcement::query()->firstOrFail();
        Queue::assertPushed(PublishAnnouncement::class, fn (PublishAnnouncement $job) => $job->announcementId === $announcement->id);
        $active = User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);
        (new PublishAnnouncement($announcement->id))->handle();
        $this->assertDatabaseHas('user_notifications', ['announcement_id' => $announcement->id, 'user_id' => $admin->id]);
        $this->assertDatabaseHas('user_notifications', ['announcement_id' => $announcement->id, 'user_id' => $active->id]);
        $this->assertDatabaseCount('user_notifications', 2);
        Queue::assertPushed(SendWhatsappNotification::class, 2);
    }

    public function test_notification_inbox_is_private_and_can_be_marked_read(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $announcement = Announcement::query()->create(['user_id' => $owner->id, 'title' => 'Info', 'body' => 'Pesan']);
        $notification = UserNotification::query()->create(['user_id' => $owner->id, 'announcement_id' => $announcement->id, 'title' => 'Info', 'body' => 'Pesan']);
        $this->actingAs($other)->post(route('notifications.read', $notification))->assertForbidden();
        $this->actingAs($owner)->post(route('notifications.read', $notification))->assertSessionHasNoErrors();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_whatsapp_job_uses_fonnte_with_a_raw_authorization_token(): void
    {
        config()->set('services.fonnte.token', 'fonnte-token');
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true])]);
        $user = User::factory()->create(['phone' => '08123456789']);
        $announcement = Announcement::query()->create(['user_id' => $user->id, 'title' => 'Info', 'body' => 'Pesan']);
        $notification = UserNotification::query()->create(['user_id' => $user->id, 'announcement_id' => $announcement->id, 'title' => 'Info', 'body' => 'Pesan']);
        (new SendWhatsappNotification($notification->id))->handle();
        $this->assertDatabaseHas('user_notifications', ['id' => $notification->id, 'whatsapp_status' => 'sent']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'fonnte-token') && $request['target'] === '08123456789');
    }

    public function test_whatsapp_job_retries_temporary_fonnte_failures(): void
    {
        config()->set('services.fonnte.token', 'fonnte-token');
        Http::fake(['https://api.fonnte.com/send' => Http::response([], 503)]);
        $user = User::factory()->create(['phone' => '08123456789']);
        $announcement = Announcement::query()->create(['user_id' => $user->id, 'title' => 'Info', 'body' => 'Pesan']);
        $notification = UserNotification::query()->create(['user_id' => $user->id, 'announcement_id' => $announcement->id, 'title' => 'Info', 'body' => 'Pesan']);

        try {
            (new SendWhatsappNotification($notification->id))->handle();
            $this->fail('Temporary Fonnte failures must be retried.');
        } catch (RuntimeException) {
            $this->assertDatabaseHas('user_notifications', ['id' => $notification->id, 'whatsapp_status' => 'queued']);
            $this->assertSame([10, 60], (new SendWhatsappNotification($notification->id))->backoff);
        }
    }

    private function userWithPermission(string $permission): User
    {
        $permission = Permission::query()->create(['slug' => $permission, 'name' => $permission]);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
