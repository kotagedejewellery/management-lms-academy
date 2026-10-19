<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\LocalCustomerServiceDealmakerSeeder;
use Database\Seeders\LocalTestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalCustomerServiceDealmakerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_customer_service_course_without_duplication(): void
    {
        $this->seed(LocalTestingSeeder::class);

        $this->seed(LocalCustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();

        $this->assertSame('published', $course->status);
        $this->assertSame(10, $course->modules()->count());
        $this->assertSame(20, Lesson::query()->whereIn('module_id', $course->modules()->pluck('id'))->count());
        $this->assertDatabaseHas('lessons', [
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/Z7OevY3UT_3y3nbIKMstkw/view',
        ]);
        $this->assertDatabaseHas('lessons', ['type' => 'video', 'resource_url' => 'https://youtu.be/k1FO1xfwqHI']);
        $this->assertDatabaseHas('course_trainers', ['course_id' => $course->id, 'user_id' => User::query()->where('email', 'trainer@kgj.test')->valueOrFail('id')]);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => User::query()->where('email', 'anisa@kgj.test')->valueOrFail('id'), 'status' => 'active']);

        $this->seed(LocalCustomerServiceDealmakerSeeder::class);

        $this->assertSame(6, Course::query()->count());
        $this->assertSame(10, $course->modules()->count());
        $this->assertSame(1, Enrollment::query()->where('course_id', $course->id)->count());
    }
}
