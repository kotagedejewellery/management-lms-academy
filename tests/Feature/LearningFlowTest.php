<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearningFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_assigns_published_course_and_employee_completes_a_lesson(): void
    {
        $admin = $this->enrollmentAdmin();
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();

        $this->actingAs($admin)->post(route('enrollments.store'), [
            'user_id' => $employee->id,
            'course_id' => $course->id,
        ])->assertRedirect(route('enrollments.index'));

        $this->actingAs($employee)->get(route('my-learning.index'))->assertOk();
        $this->actingAs($employee)->get(route('my-learning.show', $course))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('learning/Show')
                ->where('course.progress', [
                    'completed_lessons' => 0,
                    'total_lessons' => 1,
                    'completed_required_lessons' => 0,
                    'required_lessons' => 1,
                ])
                ->where('selected_lesson.content', 'Selamat datang.')
                ->missing('course.modules.0.lessons.0.content'),
            );

        $lesson = $course->modules->first()->lessons->first();

        $this->actingAs($employee)->post(route('my-learning.lessons.complete', [$course, $lesson]))
            ->assertRedirect(route('my-learning.show', ['course' => $course, 'lesson' => $lesson]));

        $this->assertDatabaseHas('lesson_progress', ['lesson_id' => $lesson->id, 'status' => 'completed']);
        $this->assertDatabaseHas('enrollments', ['user_id' => $employee->id, 'course_id' => $course->id]);
    }

    public function test_enrolled_employee_receives_a_canva_presentation_in_the_lesson_player(): void
    {
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->modules->first()->lessons->first();
        $lesson->update([
            'type' => 'presentation',
            'content' => null,
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/view?embed',
        ]);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($employee)->get(route('my-learning.show', ['course' => $course, 'lesson' => $lesson]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('learning/Show')
                ->where('selected_lesson.type', 'presentation')
                ->where('selected_lesson.resource_url', 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/view?embed'));
    }

    public function test_module_pre_test_does_not_expose_its_lesson_in_the_player(): void
    {
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();
        $module = $course->modules->first();
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'is_required' => true, 'passing_grade' => 0, 'attempt_limit' => 3]);
        $assessment->questions()->create(['prompt' => 'Siap?', 'points' => 1, 'sequence' => 1])->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($employee)->get(route('my-learning.show', $course))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('selected_lesson', null)
                ->where('course.modules.0.status', 'locked')
                ->where('course.modules.0.lessons.0.locked', true)
                ->where('course.modules.0.assessments.0.ready', true)
                ->where('course.modules.0.assessments.0.can_start', true));
    }

    public function test_employee_is_guided_to_the_module_pre_test_when_requesting_a_locked_lesson(): void
    {
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();
        $module = $course->modules->first();
        $lesson = $module->lessons->first();
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'is_required' => true, 'passing_grade' => 0, 'attempt_limit' => 1]);
        $assessment->questions()->create(['prompt' => 'Siap?', 'points' => 1, 'sequence' => 1])->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($employee)
            ->get(route('my-learning.show', ['course' => $course, 'lesson' => $lesson]))
            ->assertRedirect(route('my-learning.show', $course))
            ->assertSessionHasErrors(['action' => 'Selesaikan Pre-Test Modul Mulai di KGJ sebelum membuka materi.']);
    }

    public function test_employee_cannot_access_another_employees_course(): void
    {
        $course = $this->publishedCourseWithLesson();

        $this->actingAs(User::factory()->create())
            ->get(route('my-learning.show', $course))
            ->assertNotFound();
    }

    public function test_employee_cannot_bypass_a_course_prerequisite(): void
    {
        $employee = User::factory()->create();
        $prerequisite = $this->publishedCourseWithLesson('ONB-001');
        $course = $this->publishedCourseWithLesson('ONB-002');
        $course->update(['prerequisite_course_id' => $prerequisite->id]);
        $employee->enrollments()->create(['course_id' => $prerequisite->id, 'status' => 'active']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($employee)->get(route('my-learning.show', $course))->assertForbidden();

        $employee->enrollments()->where('course_id', $prerequisite->id)->update(['status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($employee)->get(route('my-learning.show', $course))->assertOk();
    }

    public function test_my_learning_explains_when_an_assigned_course_is_locked_by_prerequisite(): void
    {
        $employee = User::factory()->create();
        $prerequisite = $this->publishedCourseWithLesson('ONB-PREREQ');
        $course = $this->publishedCourseWithLesson('ONB-LOCKED');
        $course->update(['prerequisite_course_id' => $prerequisite->id]);
        $employee->enrollments()->create(['course_id' => $prerequisite->id, 'status' => 'active', 'due_at' => today()]);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active', 'due_at' => today()->addDay()]);

        $this->actingAs($employee)->get(route('my-learning.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('enrollments.data.1.course.id', $course->id)
                ->where('enrollments.data.1.locked', true)
                ->where('enrollments.data.1.lock_reason', 'Selesaikan course prasyarat terlebih dahulu.'));
    }

    public function test_terminal_enrollment_cannot_change_lesson_progress(): void
    {
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->modules->first()->lessons->first();
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($employee)
            ->post(route('my-learning.lessons.complete', [$course, $lesson]))
            ->assertNotFound();

        $this->assertDatabaseMissing('lesson_progress', ['lesson_id' => $lesson->id]);
    }

    public function test_employee_video_resume_position_is_saved_without_marking_the_lesson_complete(): void
    {
        $employee = User::factory()->create();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->modules->first()->lessons->first();
        $lesson->update(['type' => 'video']);
        $employee->enrollments()->create(['course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($employee)
            ->put(route('my-learning.lessons.playback', [$course, $lesson]), ['seconds' => 42])
            ->assertNoContent();

        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'status' => 'in_progress',
            'playback_seconds' => 42,
        ]);
    }

    private function publishedCourseWithLesson(string $code = 'ONB-001'): Course
    {
        $category = CourseCategory::query()->firstOrCreate(['name' => 'Onboarding'], ['is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => $code, 'title' => 'Pengenalan KGJ '.$code, 'passing_grade' => 70, 'status' => 'published']);
        $module = $course->modules()->create(['title' => 'Mulai di KGJ', 'sequence' => 1]);
        $module->lessons()->create(['title' => 'Sambutan', 'type' => 'article', 'content' => 'Selamat datang.', 'is_required' => true, 'sequence' => 1]);

        return $course->load('modules.lessons');
    }

    private function enrollmentAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment belajar']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
