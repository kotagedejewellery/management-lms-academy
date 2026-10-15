<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsappNotification;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class DeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_deadline_reminders_are_sent_once_for_three_days_and_due_today(): void
    {
        Queue::fake();
        $user = User::factory()->create(['status' => 'active']);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-REMINDER', 'title' => 'Orientasi', 'status' => 'published']);
        $enrollment = Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'active', 'due_at' => today()->addDays(3)]);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);
        $inactiveEnrollment = Enrollment::query()->create(['user_id' => $inactiveUser->id, 'course_id' => $course->id, 'status' => 'active', 'due_at' => today()->addDays(3)]);

        $this->assertCommandSucceeds();
        $this->assertCommandSucceeds();

        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'dedupe_key' => "deadline:{$enrollment->id}:".today()->addDays(3)->format('Ymd').':3']);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $inactiveUser->id, 'dedupe_key' => "deadline:{$inactiveEnrollment->id}:".today()->addDays(3)->format('Ymd').':3']);
        Queue::assertPushed(SendWhatsappNotification::class, 1);
    }

    private function assertCommandSucceeds(): void
    {
        $result = $this->artisan('learning:send-deadline-reminders');

        if ($result instanceof PendingCommand) {
            $result->assertSuccessful();

            return;
        }

        $this->assertSame(0, $result);
    }
}
