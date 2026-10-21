<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_only_sees_their_own_learning_progress(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'status' => 'published']);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $enrollment->lessonProgress()->create(['lesson_id' => $lesson->id, 'status' => 'completed', 'completed_at' => now()]);
        $otherEmployee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);
        $followUpCourse = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-002', 'title' => 'Tindak lanjut', 'status' => 'published']);
        $employee->enrollments()->create(['course_id' => $followUpCourse->id, 'status' => 'not_passed', 'best_post_score' => 50]);

        $this->actingAs($employee)->get(route('my-progress.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('learning/Progress')
            ->where('stats.active', 1)
            ->has('enrollments.data', 2)
            ->where('enrollments.data.1.status', 'needs_guidance')
            ->missing('enrollments.data.1.best_post_score'));
        $this->actingAs($employee)->get(route('my-learning.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('enrollments.data.1.status', 'needs_guidance'));
    }
}
