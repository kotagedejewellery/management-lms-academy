<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CustomerServiceDealmakerSeeder;
use Database\Seeders\ProductionCustomerServiceDealmakerForceRefreshSeeder;
use Database\Seeders\ProductionCustomerServiceDealmakerRefreshSeeder;
use Database\Seeders\ProductionOperationalDataResetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProductionCustomerServiceDealmakerRefreshSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_only_the_customer_service_learning_content_without_changing_users_or_enrollments(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        $user = User::factory()->create();
        $enrollment = Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $otherCourse = Course::query()->create([
            'category_id' => $course->category_id,
            'code' => 'UNTOUCHED-001',
            'title' => 'Course lain',
            'status' => 'draft',
        ]);

        $this->seed(ProductionCustomerServiceDealmakerRefreshSeeder::class);

        $refreshedCourse = Course::query()->where('code', 'CS-DEAL-001')->sole();

        $this->assertSame($course->id, $refreshedCourse->id);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'user_id' => $user->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('courses', ['id' => $otherCourse->id, 'code' => 'UNTOUCHED-001']);
        $this->assertSame(10, $refreshedCourse->modules()->count());
        $this->assertSame(10, Lesson::query()->whereIn('module_id', $refreshedCourse->modules()->select('id'))->count());
        $this->assertSame(10, Lesson::query()->where('type', 'video')->count());
        $this->assertSame(0, Lesson::query()->where('type', 'presentation')->count());
        $this->assertSame(18, Assessment::query()->where('course_id', $refreshedCourse->id)->count());
        $this->assertSame(180, Question::query()->count());
    }

    public function test_it_refuses_to_replace_content_when_a_learner_has_assessment_history(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        $enrollment = User::factory()->create()->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->where('course_id', $course->id)->firstOrFail();
        $assessment->attempts()->create([
            'enrollment_id' => $enrollment->id,
            'attempt_number' => 1,
            'score' => 100,
            'passed' => true,
            'submitted_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('riwayat belajar peserta');

        $this->seed(ProductionCustomerServiceDealmakerRefreshSeeder::class);

        $this->assertSame(20, Lesson::query()->whereIn('module_id', $course->modules()->select('id'))->count());
    }

    public function test_it_force_refreshes_customer_service_content_without_deleting_the_user_or_enrollment(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        $user = User::factory()->create();
        $enrollment = Enrollment::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
        ]);
        $lesson = Lesson::query()->whereIn('module_id', $course->modules()->select('id'))->firstOrFail();
        LessonProgress::query()->create(['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id]);
        $assessment = Assessment::query()->where('course_id', $course->id)->firstOrFail();
        $assessment->attempts()->create([
            'enrollment_id' => $enrollment->id,
            'attempt_number' => 1,
            'score' => 100,
            'passed' => true,
            'submitted_at' => now(),
        ]);

        $this->seed(ProductionCustomerServiceDealmakerForceRefreshSeeder::class);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'started_at' => null,
            'completed_at' => null,
        ]);
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->assertDatabaseCount('assessment_attempts', 0);
        $this->assertSame(10, Lesson::query()->whereIn('module_id', $course->modules()->select('id'))->count());
        $this->assertSame(18, Assessment::query()->where('course_id', $course->id)->count());
        $this->assertSame(180, Question::query()->count());
    }

    public function test_it_resets_operational_data_without_deleting_users_or_their_roles(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Custom Role', 'slug' => 'custom-role']);
        $user->roles()->attach($role);
        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->seed(ProductionOperationalDataResetSeeder::class);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role_id' => $role->id]);
        $this->assertDatabaseCount('enrollments', 0);
        $this->assertSame(1, Course::query()->count());
        $this->assertSame(180, Question::query()->count());
    }
}
