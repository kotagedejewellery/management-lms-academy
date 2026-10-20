<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_admin_can_set_and_update_assessment_passing_grade(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-004', 'title' => 'Pengenalan', 'passing_grade' => 70]);
        $course->trainers()->attach($admin);

        $this->actingAs($admin)->post(route('courses.assessments.store', $course), ['type' => 'post_test', 'passing_grade' => 65, 'time_limit_minutes' => 30])
            ->assertRedirect(route('courses.assessments.index', $course));

        $assessment = Assessment::query()->sole();
        $this->assertSame(65, $assessment->passing_grade);
        $this->assertSame(30, $assessment->time_limit_minutes);

        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 80])
            ->assertRedirect(route('courses.assessments.index', $course));

        $this->assertDatabaseHas('assessments', ['id' => $assessment->id, 'passing_grade' => 80]);
        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 101])->assertSessionHasErrors('passing_grade');
    }

    public function test_pre_test_is_always_a_single_attempt_baseline(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-005', 'title' => 'Pengenalan', 'passing_grade' => 70]);
        $course->trainers()->attach($admin);

        $this->actingAs($admin)->post(route('courses.assessments.store', $course), [
            'type' => 'pre_test',
            'passing_grade' => 80,
            'attempt_limit' => 3,
            'is_required' => true,
        ])->assertRedirect(route('courses.assessments.index', $course));

        $assessment = Assessment::query()->sole();
        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'passing_grade' => 0,
            'attempt_limit' => 1,
            'is_required' => true,
        ]);

        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), [
            'passing_grade' => 100,
            'attempt_limit' => 3,
            'is_required' => true,
        ])->assertRedirect(route('courses.assessments.index', $course));

        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'passing_grade' => 0,
            'attempt_limit' => 1,
        ]);
    }

    public function test_course_admin_can_create_optional_pre_and_post_tests_for_a_module(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-MODULE-001', 'title' => 'Pengenalan', 'passing_grade' => 70]);
        $module = $course->modules()->create(['title' => 'Produk', 'sequence' => 1]);
        $course->trainers()->attach($admin);

        foreach (['pre_test', 'post_test'] as $type) {
            $this->actingAs($admin)->post(route('courses.assessments.store', $course), [
                'module_id' => $module->id,
                'type' => $type,
                'passing_grade' => $type === 'pre_test' ? 0 : 70,
                'attempt_limit' => 3,
            ])->assertRedirect(route('courses.assessments.index', [$course, 'module' => $module->id]));
        }

        $this->assertDatabaseCount('assessments', 2);
        $this->assertDatabaseHas('assessments', ['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'is_required' => true]);
        $this->assertDatabaseHas('assessments', ['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'is_required' => true]);
        $this->assertFalse($course->fresh()->requires_post_test);
    }

    public function test_published_course_assessments_cannot_be_created_or_changed(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-PUBLISHED-EVAL-001', 'title' => 'Evaluasi terkunci', 'passing_grade' => 70, 'status' => 'published']);
        $course->trainers()->attach($admin);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $firstQuestion = $assessment->questions()->create(['prompt' => 'Pertanyaan satu', 'points' => 1, 'sequence' => 1]);
        $firstQuestion->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $firstQuestion->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);
        $secondQuestion = $assessment->questions()->create(['prompt' => 'Pertanyaan dua', 'points' => 1, 'sequence' => 2]);
        $secondQuestion->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $secondQuestion->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($admin)->post(route('courses.assessments.store', $course), ['type' => 'post_test', 'passing_grade' => 70])->assertStatus(422);
        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 10])->assertStatus(422);
        $this->actingAs($admin)->post(route('courses.assessments.questions.store', [$course, $assessment]), [
            'prompt' => 'Pertanyaan baru', 'points' => 1, 'answers' => ['Ya', 'Tidak'], 'correct_answer' => 0,
        ])->assertStatus(422);
        $this->actingAs($admin)->delete(route('courses.assessments.questions.destroy', [$course, $assessment, $firstQuestion]))->assertStatus(422);
        $this->actingAs($admin)->patch(route('courses.assessments.questions.move', [$course, $assessment, $secondQuestion]), ['direction' => 'up'])->assertStatus(422);
    }

    public function test_assessment_with_attempt_history_cannot_be_changed_even_after_course_returns_to_draft(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-HISTORY-EVAL-001', 'title' => 'Evaluasi bersejarah', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Pertanyaan awal', 'points' => 1, 'sequence' => 1]);
        $question->answers()->createMany([
            ['content' => 'Ya', 'is_correct' => true, 'sequence' => 1],
            ['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2],
        ]);
        $assessment->attempts()->create(['enrollment_id' => User::factory()->create()->enrollments()->create(['course_id' => $course->id])->id, 'attempt_number' => 1, 'score' => 100, 'submitted_at' => now()]);

        $this->actingAs($admin)->patch(route('courses.assessments.update', [$course, $assessment]), ['passing_grade' => 10])->assertStatus(422);
        $this->actingAs($admin)->post(route('courses.assessments.questions.store', [$course, $assessment]), [
            'prompt' => 'Pertanyaan baru', 'points' => 1, 'answers' => ['Ya', 'Tidak'], 'correct_answer' => 0,
        ])->assertStatus(422);
        $this->actingAs($admin)->put(route('courses.assessments.questions.update', [$course, $assessment, $question]), [
            'prompt' => 'Pertanyaan diubah', 'points' => 1, 'answers' => ['Ya', 'Tidak'], 'correct_answer' => 0,
        ])->assertStatus(422);
    }

    public function test_course_admin_can_edit_a_draft_question_and_its_answer_key(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-EDIT-QUESTION-001', 'title' => 'Edit soal', 'passing_grade' => 70]);
        $course->trainers()->attach($admin);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Jawaban awal?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->createMany([
            ['content' => 'Pilihan awal benar', 'is_correct' => true, 'sequence' => 1],
            ['content' => 'Pilihan awal salah', 'is_correct' => false, 'sequence' => 2],
        ]);

        $this->actingAs($admin)->put(route('courses.assessments.questions.update', [$course, $assessment, $question]), [
            'prompt' => 'Jawaban yang diperbarui?',
            'explanation' => 'Pembahasan baru.',
            'points' => 5,
            'answers' => ['Pilihan pertama', 'Pilihan kedua'],
            'correct_answer' => 1,
        ])->assertRedirect(route('courses.assessments.index', $course));

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'prompt' => 'Jawaban yang diperbarui?',
            'explanation' => 'Pembahasan baru.',
            'points' => 5,
        ]);
        $this->assertDatabaseHas('answers', ['question_id' => $question->id, 'content' => 'Pilihan kedua', 'is_correct' => true]);
        $this->assertDatabaseHas('answers', ['question_id' => $question->id, 'content' => 'Pilihan pertama', 'is_correct' => false]);
    }

    private function courseAdmin(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-courses', 'name' => 'Kelola course']);
        $role = Role::query()->create(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
