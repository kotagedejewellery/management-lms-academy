<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningPath;
use App\Models\LearningPathRule;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LocalTestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalTestingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_connected_local_demo_data_without_duplication(): void
    {
        Storage::fake('local');

        $this->seed(LocalTestingSeeder::class);

        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('courses', 5);
        $this->assertDatabaseCount('enrollments', 7);
        $this->assertDatabaseCount('assessments', 2);
        $this->assertDatabaseCount('assessment_attempts', 6);
        $this->assertDatabaseCount('learning_path_rules', 2);
        $this->assertDatabaseCount('announcements', 2);
        $this->assertDatabaseCount('activity_logs', 7);
        $this->assertDatabaseCount('certificates', 2);
        $this->assertDatabaseHas('user_roles', ['user_id' => User::query()->where('email', 'anisa@kgj.test')->valueOrFail('id'), 'role_id' => Role::query()->where('slug', 'employee')->valueOrFail('id')]);
        $this->assertDatabaseHas('enrollments', ['user_id' => User::query()->where('email', 'anisa@kgj.test')->valueOrFail('id'), 'course_id' => Course::query()->where('code', 'ONB-001')->valueOrFail('id'), 'status' => 'completed']);
        $this->assertDatabaseHas('assessment_responses', ['is_correct' => true]);
        $this->assertDatabaseHas('user_notifications', ['whatsapp_status' => 'failed']);
        Storage::disk('local')->assertExists('demo-resources/pedoman-etika-kerja.pdf');
        Storage::disk('local')->assertExists('exports/demo-progres-pembelajaran.csv');
        $this->assertDatabaseHas('certificates', ['user_id' => User::query()->where('email', 'anisa@kgj.test')->valueOrFail('id')]);
        $this->assertSame(1, Assessment::query()->where('type', 'pre_test')->sole()->attempt_limit);

        $this->seed(LocalTestingSeeder::class);

        $this->assertSame(9, User::query()->count());
        $this->assertSame(7, Enrollment::query()->count());
        $this->assertSame(2, LearningPathRule::query()->count());
    }

    public function test_it_preserves_unrelated_local_data_when_demo_data_is_refreshed(): void
    {
        Storage::fake('local');
        $customUser = User::factory()->create(['email' => 'custom.local@example.test']);

        $this->seed(LocalTestingSeeder::class);

        $this->assertDatabaseHas('users', ['id' => $customUser->id, 'email' => 'custom.local@example.test']);
    }

    public function test_seeded_data_loads_the_core_admin_manager_and_learner_pages(): void
    {
        Storage::fake('local');
        $this->seed(LocalTestingSeeder::class);

        $admin = User::query()->where('email', 'admin@kgj.test')->sole();
        $learner = User::query()->where('email', 'bima@kgj.test')->sole();
        $completedLearner = User::query()->where('email', 'citra@kgj.test')->sole();
        $manager = User::query()->where('email', 'manager@kgj.test')->sole();
        $course = Course::query()->where('code', 'ONB-001')->sole();
        $module = $course->modules()->firstOrFail();
        $lesson = $module->lessons()->firstOrFail();
        $preTest = $course->assessments()->where('type', 'pre_test')->sole();
        $postTest = $course->assessments()->where('type', 'post_test')->sole();
        $path = LearningPath::query()->where('name', 'Onboarding Karyawan Baru')->sole();

        foreach ([
            route('dashboard'), route('users.index'), route('organization.index', 'branches'), route('organization.index', 'divisions'),
            route('organization.index', 'positions'), route('course-categories.index'), route('courses.index'), route('courses.content', $course),
            route('courses.assessments.index', $course), route('learning-paths.index'), route('learning-paths.content', $path),
            route('enrollments.index'), route('learning-path-assignments.index'), route('announcements.index'), route('reports.index'), route('audit-logs.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        foreach ([
            route('dashboard'), route('my-learning.index'), route('my-learning-paths.index'), route('course-library.index'), route('my-learning.show', $course),
            route('my-progress.index'), route('notifications.index'), route('certificates.index'),
        ] as $url) {
            $this->actingAs($learner)->get($url)->assertOk();
        }

        $this->actingAs($learner)->get(route('my-learning.assessments.result', [$course, $preTest]))->assertOk();
        $this->actingAs($completedLearner)->get(route('my-learning.assessments.result', [$course, $postTest]))->assertOk();
        $this->actingAs($manager)->get(route('team-progress.index'))->assertOk();
    }
}
