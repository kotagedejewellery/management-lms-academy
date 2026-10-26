<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
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

    public function test_it_seeds_pre_and_post_tests_for_all_customer_service_modules_except_module_six(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        $modules = $course->modules()->orderBy('sequence')->get();

        $this->assertSame(18, Assessment::query()->where('course_id', $course->id)->count());
        $this->assertSame(0, Assessment::query()->where('module_id', $modules[5]->id)->count());

        foreach ($modules->reject(fn (CourseModule $module): bool => $module->sequence === 6) as $module) {
            $preTest = Assessment::query()->where('module_id', $module->id)->where('type', 'pre_test')->sole();
            $postTest = Assessment::query()->where('module_id', $module->id)->where('type', 'post_test')->sole();

            $this->assertSame(1, $preTest->attempt_limit);
            $this->assertSame(0, $preTest->passing_grade);
            $this->assertSame(10, $preTest->questions()->count());
            $this->assertSame(10, $postTest->questions()->count());
            $this->assertSame(80, $postTest->passing_grade);
            $this->assertSame(3, $postTest->attempt_limit);

            foreach ([$preTest, $postTest] as $assessment) {
                foreach ($assessment->questions()->with('answers')->get() as $question) {
                    $this->assertSame(1, $question->answers->where('is_correct', true)->count());
                }
            }

            $this->assertCount(
                0,
                $preTest->questions()->pluck('prompt')->intersect($postTest->questions()->pluck('prompt')),
            );
        }
    }

    public function test_it_does_not_change_an_assessment_with_attempt_history(): void
    {
        $this->seed(CustomerServiceDealmakerSeeder::class);

        $course = Course::query()->where('code', 'CS-DEAL-001')->sole();
        $assessment = Assessment::query()->where('course_id', $course->id)->where('type', 'post_test')->firstOrFail();
        $question = $assessment->questions()->firstOrFail();
        $question->update(['prompt' => 'Pertanyaan historis tidak boleh berubah.']);
        $enrollment = User::factory()->create()->enrollments()->create(['course_id' => $course->id]);
        $assessment->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'submitted_at' => now()]);

        $this->seed(CustomerServiceDealmakerSeeder::class);

        $this->assertSame('Pertanyaan historis tidak boleh berubah.', $question->fresh()->prompt);
    }
}
