<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_requires_a_lesson_before_it_can_be_published(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);

        $this->actingAs($admin)->post(route('courses.store'), [
            'category_id' => $category->id,
            'code' => 'ONB-001',
            'title' => 'Pengenalan KGJ',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
        ])->assertRedirect();

        $courseId = (int) \DB::table('courses')->value('id');

        $this->actingAs($admin)->put(route('courses.update', $courseId), [
            'category_id' => $category->id,
            'code' => 'ONB-001',
            'title' => 'Pengenalan KGJ',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'published',
        ])->assertSessionHasErrors('status');

        $this->actingAs($admin)->post(route('courses.modules.store', $courseId), [
            'title' => 'Mulai di KGJ',
            'sequence' => 1,
        ])->assertRedirect();

        $moduleId = (int) \DB::table('modules')->value('id');

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $courseId, 'module' => $moduleId]), [
            'title' => 'Sambutan',
            'type' => 'article',
            'content' => 'Selamat datang di KGJ Academy.',
            'is_required' => true,
            'sequence' => 1,
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('courses.update', $courseId), [
            'category_id' => $category->id,
            'code' => 'ONB-001',
            'title' => 'Pengenalan KGJ',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'published',
        ])->assertRedirect(route('courses.content', $courseId));

        $this->assertDatabaseHas('courses', ['id' => $courseId, 'status' => 'published']);
    }

    public function test_course_content_appends_and_reorders_modules_and_lessons_without_manual_sequences(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-ORDER-001', 'title' => 'Urutan materi', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);

        $this->actingAs($admin)->post(route('courses.modules.store', $course), ['title' => 'Modul pertama'])->assertRedirect();
        $this->actingAs($admin)->post(route('courses.modules.store', $course), ['title' => 'Modul kedua'])->assertRedirect();

        $firstModule = $course->modules()->where('title', 'Modul pertama')->firstOrFail();
        $secondModule = $course->modules()->where('title', 'Modul kedua')->firstOrFail();
        $this->assertDatabaseHas('modules', ['id' => $firstModule->id, 'sequence' => 1]);
        $this->assertDatabaseHas('modules', ['id' => $secondModule->id, 'sequence' => 2]);

        $this->actingAs($admin)->patch(route('courses.modules.move', [$course, $firstModule]), ['direction' => 'down'])->assertRedirect();
        $this->assertDatabaseHas('modules', ['id' => $firstModule->id, 'sequence' => 2]);
        $this->assertDatabaseHas('modules', ['id' => $secondModule->id, 'sequence' => 1]);

        $this->actingAs($admin)->delete(route('courses.modules.destroy', [$course, $secondModule]))->assertRedirect();
        $this->assertDatabaseHas('modules', ['id' => $firstModule->id, 'sequence' => 1]);

        $this->actingAs($admin)->post(route('courses.lessons.store', [$course, $firstModule]), [
            'title' => 'Lesson pertama',
            'type' => 'article',
            'content' => 'Isi pertama',
            'is_required' => true,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('courses.lessons.store', [$course, $firstModule]), [
            'title' => 'Lesson kedua',
            'type' => 'article',
            'content' => 'Isi kedua',
            'is_required' => true,
        ])->assertRedirect();

        $firstLesson = $firstModule->lessons()->where('title', 'Lesson pertama')->firstOrFail();
        $secondLesson = $firstModule->lessons()->where('title', 'Lesson kedua')->firstOrFail();
        $this->assertDatabaseHas('lessons', ['id' => $firstLesson->id, 'sequence' => 1]);
        $this->assertDatabaseHas('lessons', ['id' => $secondLesson->id, 'sequence' => 2]);

        $this->actingAs($admin)->patch(route('courses.lessons.move', [$course, $firstModule, $firstLesson]), ['direction' => 'down'])->assertRedirect();
        $this->assertDatabaseHas('lessons', ['id' => $firstLesson->id, 'sequence' => 2]);
        $this->assertDatabaseHas('lessons', ['id' => $secondLesson->id, 'sequence' => 1]);

        $this->actingAs($admin)->delete(route('courses.lessons.destroy', [$course, $firstModule, $secondLesson]))->assertRedirect();
        $this->assertDatabaseHas('lessons', ['id' => $firstLesson->id, 'sequence' => 1]);
    }

    public function test_course_admin_can_reorder_assessment_questions_without_manual_sequences(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-QUESTION-001', 'title' => 'Urutan soal', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);

        foreach (['Soal pertama', 'Soal kedua'] as $prompt) {
            $this->actingAs($admin)->post(route('courses.assessments.questions.store', [$course, $assessment]), [
                'prompt' => $prompt,
                'points' => 1,
                'answers' => ['Benar', 'Salah'],
                'correct_answer' => 0,
            ])->assertRedirect();
        }

        $firstQuestion = $assessment->questions()->where('prompt', 'Soal pertama')->firstOrFail();
        $secondQuestion = $assessment->questions()->where('prompt', 'Soal kedua')->firstOrFail();
        $this->assertDatabaseHas('questions', ['id' => $firstQuestion->id, 'sequence' => 1]);
        $this->assertDatabaseHas('questions', ['id' => $secondQuestion->id, 'sequence' => 2]);

        $this->actingAs($admin)->patch(route('courses.assessments.questions.move', [$course, $assessment, $secondQuestion]), ['direction' => 'up'])->assertRedirect();

        $this->assertDatabaseHas('questions', ['id' => $firstQuestion->id, 'sequence' => 2]);
        $this->assertDatabaseHas('questions', ['id' => $secondQuestion->id, 'sequence' => 1]);
    }

    public function test_employee_cannot_manage_courses(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('courses.index'))
            ->assertForbidden();
    }

    public function test_trainer_cannot_create_courses_or_manage_global_categories(): void
    {
        $trainer = $this->trainer();

        $this->actingAs($trainer)->get(route('courses.create'))->assertForbidden();
        $this->actingAs($trainer)->post(route('courses.store'), [
            'category_id' => 1,
            'code' => 'TRN-001',
            'title' => 'Course mandiri',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
        ])->assertForbidden();
        $this->actingAs($trainer)->get(route('course-categories.index'))->assertForbidden();
    }

    public function test_lms_admin_can_manage_global_categories(): void
    {
        $admin = $this->courseAdmin();

        $this->actingAs($admin)->get(route('course-categories.index'))->assertOk();
        $this->actingAs($admin)->post(route('course-categories.store'), [
            'name' => 'Onboarding',
            'is_active' => true,
        ])->assertRedirect(route('course-categories.index'));

        $this->assertDatabaseHas('course_categories', ['name' => 'Onboarding', 'is_active' => true]);
    }

    public function test_course_thumbnail_is_stored_and_available_to_its_trainer(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);

        $this->actingAs($admin)->post(route('courses.store'), [
            'category_id' => $category->id,
            'code' => 'ONB-THUMB-001',
            'title' => 'Course dengan thumbnail',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
            'thumbnail' => UploadedFile::fake()->image('course.png', 1920, 1080),
        ])->assertRedirect();

        $course = Course::query()->where('code', 'ONB-THUMB-001')->sole();
        Storage::disk('local')->assertExists($course->thumbnail_path);
        $this->assertSame('image/webp', $course->thumbnail_mime);
        $this->assertStringEndsWith('.webp', $course->thumbnail_path);
        $dimensions = getimagesizefromstring(Storage::disk('local')->get($course->thumbnail_path));
        $this->assertIsArray($dimensions);
        $this->assertSame(1280, $dimensions[0]);
        $this->assertSame(720, $dimensions[1]);
        $this->actingAs($admin)->get(route('courses.thumbnail', $course))
            ->assertOk()
            ->assertHeader('Cache-Control', 'immutable, max-age=86400, private');
    }

    public function test_course_rejects_a_thumbnail_outside_the_catalog_ratio(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);

        $this->actingAs($admin)->post(route('courses.store'), [
            'category_id' => $category->id,
            'code' => 'ONB-THUMB-RATIO',
            'title' => 'Course thumbnail portrait',
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
            'thumbnail' => UploadedFile::fake()->image('portrait.png', 800, 1000),
        ])->assertSessionHasErrors([
            'thumbnail' => 'Gunakan gambar dengan rasio 16:9 agar tampil rapi di katalog.',
        ]);
    }

    public function test_course_thumbnail_can_be_removed(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $path = 'course-thumbnails/thumbnail-lama.webp';
        Storage::disk('local')->put($path, 'thumbnail lama');
        $course = Course::query()->create([
            'category_id' => $category->id,
            'code' => 'ONB-THUMB-REMOVE',
            'title' => 'Course thumbnail dihapus',
            'thumbnail_path' => $path,
            'thumbnail_disk' => 'local',
            'thumbnail_mime' => 'image/webp',
            'passing_grade' => 70,
            'status' => 'draft',
        ]);
        $course->trainers()->attach($admin);

        $this->actingAs($admin)->put(route('courses.update', $course), [
            'category_id' => $category->id,
            'code' => $course->code,
            'title' => $course->title,
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
            'remove_thumbnail' => true,
        ])->assertRedirect(route('courses.content', $course));

        $course->refresh();
        $this->assertNull($course->thumbnail_path);
        $this->assertNull($course->thumbnail_disk);
        $this->assertNull($course->thumbnail_mime);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_course_form_lists_active_trainers_for_an_admin_who_can_assign_them(): void
    {
        $admin = $this->courseAdmin();
        $admin->update(['name' => 'Admin Course']);
        $activeTrainer = User::factory()->create(['name' => 'Trainer Aktif']);
        $inactiveTrainer = User::factory()->create(['name' => 'Trainer Nonaktif', 'status' => 'inactive']);
        $trainerRole = Role::query()->create(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $activeTrainer->roles()->attach($trainerRole);
        $inactiveTrainer->roles()->attach($trainerRole);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-TRAINER-001', 'title' => 'Penugasan trainer', 'passing_grade' => 70, 'status' => 'draft']);

        $this->actingAs($admin)->get(route('courses.edit', $course))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('can_assign_trainers', true)
                ->has('trainers', 1)
                ->where('trainers.0.name', 'Trainer Aktif'));
    }

    public function test_trainer_can_only_manage_courses_assigned_to_them(): void
    {
        $trainer = $this->trainer();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $assigned = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-ASSIGNED', 'title' => 'Ditugaskan', 'passing_grade' => 70, 'status' => 'draft']);
        $other = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-OTHER', 'title' => 'Bukan tugas trainer', 'passing_grade' => 70, 'status' => 'draft']);
        $assigned->trainers()->attach($trainer);

        $this->actingAs($trainer)->get(route('courses.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('courses.data', 1));
        $this->actingAs($trainer)->get(route('courses.content', $other))->assertForbidden();
        $this->actingAs($trainer)->get(route('courses.assessments.index', $other))->assertForbidden();
        $this->actingAs($trainer)->post(route('courses.modules.store', $other), ['title' => 'Tidak boleh', 'sequence' => 1])->assertForbidden();
    }

    public function test_trainer_cannot_change_course_trainer_assignments(): void
    {
        $trainer = $this->trainer();
        $otherTrainer = User::factory()->create();
        $otherTrainer->roles()->attach(Role::query()->where('slug', 'trainer')->sole());
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-LOCKED', 'title' => 'Course terkunci', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($trainer);

        $this->actingAs($trainer)->put(route('courses.update', $course), [
            'category_id' => $category->id,
            'code' => $course->code,
            'title' => $course->title,
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
            'trainer_ids' => [$otherTrainer->id],
        ])->assertForbidden();
        $this->assertCount(1, $course->fresh()->trainers);
        $this->assertTrue($course->fresh()->trainers->contains($trainer));
    }

    public function test_course_requiring_post_test_needs_a_complete_post_test_before_publish(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-EVAL-001', 'title' => 'Evaluasi', 'passing_grade' => 70, 'requires_post_test' => true, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $course->modules()->create(['title' => 'Modul', 'sequence' => 1])->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);

        $this->actingAs($admin)->put(route('courses.update', $course), $this->publishedCoursePayload($category, $course))
            ->assertSessionHasErrors('status');

        $assessment = Assessment::query()->create(['course_id' => $course->id, 'type' => 'post_test', 'passing_grade' => 70, 'attempt_limit' => 3]);
        $question = $assessment->questions()->create(['prompt' => 'Benar?', 'points' => 1, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Ya', 'is_correct' => true, 'sequence' => 1]);
        $question->answers()->create(['content' => 'Tidak', 'is_correct' => false, 'sequence' => 2]);

        $this->actingAs($admin)->put(route('courses.update', $course), $this->publishedCoursePayload($category, $course))
            ->assertRedirect(route('courses.content', $course));
    }

    public function test_course_rejects_a_position_from_another_division(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $sales = Division::query()->create(['code' => 'SLS', 'name' => 'Sales']);
        $operations = Division::query()->create(['code' => 'OPS', 'name' => 'Operations']);
        $operationsPosition = Position::query()->create(['division_id' => $operations->id, 'name' => 'Admin Operasional']);
        $first = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pertama', 'passing_grade' => 70, 'status' => 'draft']);
        $second = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-002', 'title' => 'Kedua', 'passing_grade' => 70, 'status' => 'draft']);
        $first->trainers()->attach($admin);

        $this->actingAs($admin)->put(route('courses.update', $first), [
            'category_id' => $category->id,
            'division_id' => $sales->id,
            'position_id' => $operationsPosition->id,
            'prerequisite_course_id' => $second->id,
            'code' => $first->code,
            'title' => $first->title,
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
        ])->assertSessionHasErrors('position_id');
    }

    public function test_course_rejects_an_indirect_prerequisite_cycle(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $first = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pertama', 'passing_grade' => 70, 'status' => 'draft']);
        $second = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-002', 'title' => 'Kedua', 'passing_grade' => 70, 'prerequisite_course_id' => $first->id, 'status' => 'draft']);
        $first->trainers()->attach($admin);

        $this->actingAs($admin)->put(route('courses.update', $first), [
            'category_id' => $category->id,
            'prerequisite_course_id' => $second->id,
            'code' => $first->code,
            'title' => $first->title,
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'draft',
        ])->assertSessionHasErrors('prerequisite_course_id');
    }

    public function test_draft_lesson_can_be_deleted_but_published_course_content_cannot(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-DELETE-001', 'title' => 'Draft', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $module = $course->modules()->create(['title' => 'Modul', 'sequence' => 1]);
        $lesson = $module->lessons()->create(['title' => 'Lesson', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);

        $this->actingAs($admin)->delete(route('courses.lessons.destroy', [$course, $module, $lesson]))
            ->assertRedirect(route('courses.content', $course));
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);

        $course->update(['status' => 'published']);
        $publishedLesson = $module->lessons()->create(['title' => 'Terkunci', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);
        $this->actingAs($admin)->delete(route('courses.lessons.destroy', [$course, $module, $publishedLesson]))->assertStatus(422);
        $this->assertDatabaseHas('lessons', ['id' => $publishedLesson->id]);
    }

    public function test_course_requires_a_valid_source_for_every_media_lesson_before_publish(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-MEDIA-001', 'title' => 'Materi media', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $module = $course->modules()->create(['title' => 'Modul', 'sequence' => 1]);
        $module->lessons()->create(['title' => 'PDF tanpa sumber', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);

        $this->actingAs($admin)->put(route('courses.update', $course), [
            'category_id' => $category->id,
            'code' => $course->code,
            'title' => $course->title,
            'is_mandatory' => true,
            'passing_grade' => 70,
            'status' => 'published',
        ])->assertSessionHasErrors('status');
    }

    public function test_course_with_a_canva_presentation_can_be_published(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-CANVA-001', 'title' => 'Presentasi Canva', 'passing_grade' => 70, 'status' => 'draft']);
        $course->trainers()->attach($admin);
        $module = $course->modules()->create(['title' => 'Modul', 'sequence' => 1]);
        $module->lessons()->create([
            'title' => 'Presentasi layanan pelanggan',
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/view?embed',
            'is_required' => true,
            'sequence' => 1,
        ]);

        $this->actingAs($admin)->put(route('courses.update', $course), [
            'category_id' => $category->id,
            'code' => $course->code,
            'title' => $course->title,
            'is_mandatory' => true,
            'requires_post_test' => false,
            'passing_grade' => 70,
            'status' => 'published',
        ])->assertRedirect(route('courses.content', $course));

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'status' => 'published']);
    }

    public function test_published_course_content_cannot_be_added_or_edited(): void
    {
        $admin = $this->courseAdmin();
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-READONLY-001', 'title' => 'Terkunci', 'passing_grade' => 70, 'status' => 'published']);
        $course->trainers()->attach($admin);
        $module = $course->modules()->create(['title' => 'Modul', 'sequence' => 1]);
        $lesson = $module->lessons()->create(['title' => 'Artikel', 'type' => 'article', 'content' => 'Isi awal', 'is_required' => true, 'sequence' => 1]);

        $this->actingAs($admin)->post(route('courses.modules.store', $course), ['title' => 'Modul baru', 'sequence' => 2])->assertStatus(422);
        $this->actingAs($admin)->put(route('courses.modules.update', [$course, $module]), ['title' => 'Diubah', 'sequence' => 1])->assertStatus(422);
        $this->actingAs($admin)->post(route('courses.lessons.store', [$course, $module]), [
            'title' => 'Lesson baru', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 2,
        ])->assertStatus(422);
        $this->actingAs($admin)->put(route('courses.lessons.update', [$course, $module, $lesson]), [
            'title' => 'Artikel diubah', 'type' => 'article', 'content' => 'Isi baru', 'is_required' => true, 'sequence' => 1,
        ])->assertStatus(422);

        $this->assertDatabaseHas('modules', ['id' => $module->id, 'title' => 'Modul']);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'title' => 'Artikel', 'content' => 'Isi awal']);
    }

    /** @return array<string, int|string|bool> */
    private function publishedCoursePayload(CourseCategory $category, Course $course): array
    {
        return [
            'category_id' => $category->id,
            'code' => $course->code,
            'title' => $course->title,
            'is_mandatory' => true,
            'requires_post_test' => true,
            'passing_grade' => 70,
            'status' => 'published',
        ];
    }

    private function courseAdmin(): User
    {
        $coursePermission = Permission::query()->create(['slug' => 'manage-courses', 'name' => 'Kelola course']);
        $enrollmentPermission = Permission::query()->create(['slug' => 'manage-enrollments', 'name' => 'Kelola assignment']);
        $role = Role::query()->create(['slug' => 'lms-admin', 'name' => 'LMS Admin']);
        $role->permissions()->attach([$coursePermission->id, $enrollmentPermission->id]);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function trainer(): User
    {
        $permission = Permission::query()->create(['slug' => 'manage-courses', 'name' => 'Kelola course']);
        $role = Role::query()->create(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
