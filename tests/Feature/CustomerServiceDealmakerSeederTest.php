<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\CustomerServiceDealmakerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceDealmakerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_only_the_customer_service_catalog_without_users_or_enrollments(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();

        $this->assertSame('published', $course->status);
        $this->assertSame(10, $course->modules()->count());
        $this->assertSame(20, Lesson::query()->whereIn('module_id', $course->modules()->pluck('id'))->count());
        $this->assertDatabaseHas('lessons', [
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/Z7OevY3UT_3y3nbIKMstkw/view',
        ]);
        $this->assertDatabaseHas('lessons', ['type' => 'video', 'resource_url' => 'https://youtu.be/k1FO1xfwqHI']);
        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Enrollment::query()->count());

        $this->seed(CustomerServiceDealmakerSeeder::class);

        $this->assertSame(1, Course::query()->count());
        $this->assertSame(10, $course->modules()->count());
    }
}
