<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentRetakeAuthorization;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssessmentAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_pre_test_blocks_learning_until_it_is_submitted(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-PRE-001', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3, 'is_required' => true]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $correct = $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $lesson]))
            ->assertSessionHasErrors('pre_test');

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $lesson]))
            ->assertRedirect();
    }

    public function test_legacy_pre_test_is_limited_to_a_single_attempt(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-PRE-LEGACY-001', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3, 'is_required' => true]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $correct = $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))
            ->assertSessionHasErrors(['assessment' => 'Assessment telah ditutup. Management akan memberikan arahan bila diperlukan.']);

        $this->assertDatabaseCount('assessment_attempts', 1);
    }

    public function test_post_test_requires_lessons_then_sends_course_for_final_review(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan', 'passing_grade' => 70, 'requires_post_test' => true, 'status' => 'published']);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $correct = $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])->assertSessionHasErrors('assessment');
        $enrollment->lessonProgress()->create(['lesson_id' => $lesson->id, 'status' => 'completed', 'completed_at' => now()]);
        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));
        $this->assertDatabaseHas('assessment_attempts', ['assessment_id' => $assessment->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true]);
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $user->id, 'event' => 'assessment.submitted']);
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'pending_review']);
        $this->actingAs($user)->get(route('my-learning.assessments.receipt', [$course, $assessment]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('learning/AssessmentResult')
            ->missing('latest')
            ->missing('best_score')
            ->missing('attempts_used')
            ->missing('pre_test_score')
            ->missing('improvement')
            ->missing('review'));
    }

    public function test_course_post_test_requires_all_module_post_tests_to_be_submitted(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-COMBINED-POST-001', 'title' => 'Evaluasi lengkap', 'passing_grade' => 70, 'requires_post_test' => true, 'status' => 'published']);
        $module = $course->modules()->create(['title' => 'Modul', 'sequence' => 1]);
        $lesson = $module->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $modulePostTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'is_required' => true, 'passing_grade' => 70, 'attempt_limit' => 3]);
        $moduleQuestion = $modulePostTest->questions()->create(['prompt' => 'Modul selesai?', 'points' => 1, 'sequence' => 1]);
        $moduleAnswer = $moduleQuestion->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $coursePostTest = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);
        $courseQuestion = $coursePostTest->questions()->create(['prompt' => 'Course selesai?', 'points' => 1, 'sequence' => 1]);
        $courseAnswer = $courseQuestion->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $enrollment->lessonProgress()->create(['lesson_id' => $lesson->id, 'status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $coursePostTest]), ['answers' => [$courseQuestion->id => $courseAnswer->id]])
            ->assertSessionHasErrors('assessment');
        $this->assertDatabaseMissing('assessment_attempts', ['assessment_id' => $coursePostTest->id, 'enrollment_id' => $enrollment->id]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $modulePostTest]), ['answers' => [$moduleQuestion->id => $moduleAnswer->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $modulePostTest]));
        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $coursePostTest]), ['answers' => [$courseQuestion->id => $courseAnswer->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $coursePostTest]));

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'pending_review']);
    }

    public function test_course_without_a_required_post_test_is_sent_for_final_review_after_required_lessons(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-LESSON-001', 'title' => 'Materi mandiri', 'passing_grade' => 70, 'status' => 'published', 'requires_post_test' => false]);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);

        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $lesson]))->assertRedirect();

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'pending_review']);
    }

    public function test_module_pre_test_blocks_material_and_submitted_post_test_opens_the_next_module(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-MODULE-POST-001', 'title' => 'Materi modul', 'passing_grade' => 70, 'status' => 'published']);
        $module = $course->modules()->create(['title' => 'Produk', 'sequence' => 1]);
        $lesson = $module->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $nextModule = $course->modules()->create(['title' => 'Layanan', 'sequence' => 2]);
        $nextLesson = $nextModule->lessons()->create(['title' => 'Lesson berikutnya', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $preTest = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'pre_test', 'is_required' => true, 'passing_grade' => 0, 'attempt_limit' => 3]);
        $preQuestion = $preTest->questions()->create(['prompt' => 'Siap?', 'points' => 1, 'sequence' => 1]);
        $preAnswer = $preQuestion->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'module_id' => $module->id, 'type' => 'post_test', 'is_required' => true, 'passing_grade' => 70, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $incorrect = $question->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $lesson]))->assertSessionHasErrors('module');
        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $preTest]), ['answers' => [$preQuestion->id => $preAnswer->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $preTest]));
        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $lesson]))->assertRedirect();
        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $nextLesson]))->assertSessionHasErrors('module');
        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $incorrect->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));
        $this->actingAs($user)->post(route('my-learning.lessons.complete', [$course, $nextLesson]))->assertRedirect();
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'pending_review']);
    }

    public function test_failed_post_test_attempt_sends_the_course_for_final_review(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-FAIL-001', 'title' => 'Evaluasi', 'passing_grade' => 70, 'status' => 'published', 'requires_post_test' => true]);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $enrollment->lessonProgress()->create(['lesson_id' => $lesson->id, 'status' => 'completed', 'completed_at' => now()]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 100, 'attempt_limit' => 1]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $incorrect = $question->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $incorrect->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'pending_review']);
    }

    public function test_expired_timed_attempt_is_finalized_with_zero_score(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-002', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3, 'time_limit_minutes' => 10]);
        $attempt = $assessment->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'started_at' => now()->subMinutes(11), 'expires_at' => now()->subMinute()]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => []])->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->assertDatabaseHas('assessment_attempts', ['id' => $attempt->id, 'score' => 0]);
        $this->assertNotNull($attempt->fresh()->submitted_at);
    }

    public function test_opening_timed_assessment_starts_server_attempt(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-003', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3, 'time_limit_minutes' => 10]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))->assertOk();

        $attempt = $assessment->attempts()->sole();
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->expires_at);
        $this->assertNull($attempt->submitted_at);
    }

    public function test_answers_are_saved_as_a_draft_without_consuming_an_attempt(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-004', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $correct = $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->put(route('my-learning.assessments.draft', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])->assertNoContent();

        $this->assertDatabaseHas('assessment_drafts', ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id]);
        $this->assertDatabaseHas('assessment_attempts', ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id, 'submitted_at' => null]);
        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))->assertInertia(fn (Assert $page) => $page->where("assessment.draft_answers.{$question->id}", $correct->id));

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => []])->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->assertDatabaseHas('assessment_attempts', ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 100]);
        $this->assertDatabaseMissing('assessment_drafts', ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id]);
    }

    public function test_assessment_receipt_hides_internal_result_details_and_legacy_result_url_redirects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-005', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Apa jawabannya?', 'explanation' => 'Ya adalah jawaban yang benar.', 'points' => 1, 'sequence' => 1]);
        $correct = $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $correct->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));

        $this->actingAs($user)->get(route('my-learning.assessments.receipt', [$course, $assessment]))->assertInertia(fn (Assert $page) => $page
            ->component('learning/AssessmentResult')
            ->where('assessment.type', 'pre_test')
            ->missing('latest')
            ->missing('best_score')
            ->missing('attempts_used')
            ->missing('pre_test_score')
            ->missing('improvement')
            ->missing('review'),
        );
        $this->actingAs($user)->get(route('my-learning.show', $course))->assertInertia(fn (Assert $page) => $page
            ->missing('enrollment.status')
            ->missing('course.assessments.0.attempt_limit')
            ->missing('course.assessments.0.attempts_used')
            ->missing('course.assessments.0.best_score')
            ->missing('course.assessments.0.latest_attempt'),
        );
        $this->actingAs($user)->get(route('my-learning.assessments.result', [$course, $assessment]))
            ->assertRedirect(route('my-learning.show', $course));
        $this->actingAs($otherUser)->get(route('my-learning.assessments.receipt', [$course, $assessment]))->assertNotFound();
    }

    public function test_failed_post_test_requires_internal_authorization_before_a_retake(): void
    {
        $user = User::factory()->create();
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-RETAKE-001', 'title' => 'Evaluasi', 'passing_grade' => 70, 'requires_post_test' => true, 'status' => 'published']);
        $course->trainers()->attach($trainer);
        $lesson = $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $enrollment = $user->enrollments()->create(['course_id' => $course->id]);
        $enrollment->lessonProgress()->create(['lesson_id' => $lesson->id, 'status' => 'completed', 'completed_at' => now()]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 100, 'attempt_limit' => 2]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $incorrect = $question->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($user)->post(route('my-learning.assessments.submit', [$course, $assessment]), ['answers' => [$question->id => $incorrect->id]])
            ->assertRedirect(route('my-learning.assessments.receipt', [$course, $assessment]));
        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))
            ->assertSessionHasErrors(['assessment' => 'Assessment telah ditutup. Management akan memberikan arahan bila diperlukan.']);

        $this->actingAs($trainer)
            ->withHeader('Referer', route('trainer-progress.index'))
            ->post(route('assessment-retakes.store', [$enrollment, $assessment]))
            ->assertRedirect(route('trainer-progress.index'));

        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('learning/AssessmentStart'));
        $this->assertDatabaseHas('assessment_retake_authorizations', [
            'assessment_id' => $assessment->id,
            'enrollment_id' => $enrollment->id,
            'authorized_by' => $trainer->id,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'title' => 'Kesempatan Post-Test tersedia',
        ]);
        $this->assertNull(AssessmentRetakeAuthorization::query()->sole()->used_at);

        $this->actingAs($trainer)
            ->withHeader('Referer', route('trainer-progress.index'))
            ->delete(route('assessment-retakes.destroy', [$enrollment, $assessment]))
            ->assertRedirect(route('trainer-progress.index'));
        $this->assertNotNull(AssessmentRetakeAuthorization::query()->sole()->cancelled_at);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'pending_review',
        ]);

        $this->actingAs($user)->get(route('my-learning.assessments.show', [$course, $assessment]))
            ->assertSessionHasErrors(['assessment' => 'Assessment telah ditutup. Management akan memberikan arahan bila diperlukan.']);

        $this->actingAs($trainer)
            ->withHeader('Referer', route('trainer-progress.index'))
            ->post(route('assessment-retakes.store', [$enrollment, $assessment]))
            ->assertRedirect(route('trainer-progress.index'));
        $this->actingAs($user)
            ->post(route('my-learning.assessments.start', [$course, $assessment]))
            ->assertRedirect(route('my-learning.assessments.show', [$course, $assessment]));
        $this->assertNotNull(AssessmentRetakeAuthorization::query()->latest('id')->firstOrFail()->used_at);
    }

    public function test_completed_course_cannot_be_reopened_with_a_post_test_retake(): void
    {
        $learner = User::factory()->create();
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-COMPLETE-RETAKE-001', 'title' => 'Evaluasi selesai', 'passing_grade' => 70, 'status' => 'published']);
        $course->trainers()->attach($trainer);
        $enrollment = $learner->enrollments()->create(['course_id' => $course->id, 'status' => 'completed', 'completed_at' => now()]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 80, 'attempt_limit' => 3]);
        $assessment->attempts()->create(['enrollment_id' => $enrollment->id, 'attempt_number' => 1, 'score' => 50, 'passed' => false, 'submitted_at' => now()]);

        $this->actingAs($trainer)
            ->withHeader('Referer', route('trainer-progress.index'))
            ->post(route('assessment-retakes.store', [$enrollment, $assessment]))
            ->assertSessionHasErrors(['assessment' => 'Kesempatan ulang hanya dapat dibuka saat evaluasi akhir course.']);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'completed',
        ]);
    }

    public function test_draft_rejects_an_answer_from_a_different_question(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-006', 'title' => 'Pengenalan', 'passing_grade' => 70, 'status' => 'published']);
        $user->enrollments()->create(['course_id' => $course->id]);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'pre_test', 'passing_grade' => 0, 'attempt_limit' => 3]);
        $firstQuestion = $assessment->questions()->create(['prompt' => 'Pertanyaan satu', 'points' => 1, 'sequence' => 1]);
        $firstQuestion->answers()->create(['content' => 'Jawaban satu', 'is_correct' => true, 'sequence' => 1]);
        $secondQuestion = $assessment->questions()->create(['prompt' => 'Pertanyaan dua', 'points' => 1, 'sequence' => 2]);
        $foreignAnswer = $secondQuestion->answers()->create(['content' => 'Jawaban dua', 'is_correct' => true, 'sequence' => 1]);

        $this->actingAs($user)->put(route('my-learning.assessments.draft', [$course, $assessment]), ['answers' => [$firstQuestion->id => $foreignAnswer->id]])
            ->assertSessionHasErrors('answers');

        $this->assertDatabaseMissing('assessment_drafts', ['assessment_id' => $assessment->id]);
    }

    private function trainer(): User
    {
        $permission = Permission::query()->firstOrCreate(['slug' => 'manage-courses'], ['name' => 'Kelola course']);
        $role = Role::query()->firstOrCreate(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $trainer = User::factory()->create();
        $trainer->roles()->attach($role);

        return $trainer;
    }
}
