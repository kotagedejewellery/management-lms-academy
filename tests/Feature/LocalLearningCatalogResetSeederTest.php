<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\ReportExport;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LocalLearningCatalogResetSeeder;
use Database\Seeders\LocalTestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalLearningCatalogResetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_local_learning_data_without_deleting_users(): void
    {
        $this->seed(LocalTestingSeeder::class);

        $preservedUser = User::factory()->create();
        $preservedUser->roles()->attach(Role::query()->where('slug', 'employee')->valueOrFail('id'));
        $userCount = User::query()->count();

        $this->seed(LocalLearningCatalogResetSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();

        $this->assertSame($userCount, User::query()->count());
        $this->assertSame(1, Course::query()->count());
        $this->assertSame(0, Enrollment::query()->count());
        $this->assertSame(0, LearningPath::query()->count());
        $this->assertSame(0, Announcement::query()->count());
        $this->assertSame(0, ReportExport::query()->count());
        $this->assertDatabaseHas('user_roles', ['user_id' => $preservedUser->id]);
        $this->assertTrue(User::query()->where('email', 'trainer@kgj.test')->sole()->roles()->where('slug', 'trainer')->exists());
        $this->assertSame(10, $course->modules()->count());
        $this->assertSame(10, Lesson::query()->whereIn('module_id', $course->modules()->select('id'))->count());
        $this->assertSame(10, Lesson::query()->where('type', 'video')->count());
        $this->assertSame(0, Lesson::query()->where('type', 'presentation')->count());
        $this->assertDatabaseHas('lessons', ['type' => 'video', 'resource_url' => 'https://youtu.be/k1FO1xfwqHI']);
        $this->assertSame(18, Assessment::query()->count());
        $this->assertSame(180, Question::query()->count());
        $this->assertSame(720, Answer::query()->count());
        $this->assertSame(0, Assessment::query()->where('module_id', $course->modules()->where('sequence', 6)->valueOrFail('id'))->count());

        $this->seed(LocalLearningCatalogResetSeeder::class);

        $this->assertSame($userCount, User::query()->count());
        $this->assertSame(1, Course::query()->count());
        $this->assertSame(10, Lesson::query()->count());
        $this->assertSame(18, Assessment::query()->count());
    }
}
