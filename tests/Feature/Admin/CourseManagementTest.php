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

    public function test_employee_cannot_manage_courses(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('courses.index'))
            ->assertForbidden();
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
        ])->assertSessionHasErrors('thumbnail');
    }

    public function test_trainer_can_only_manage_courses_assigned_to_them(): void
    {
        $trainer = $this->courseAdmin();
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
        $trainer = $this->courseAdmin();
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
        $permission = Permission::query()->create(['slug' => 'manage-courses', 'name' => 'Kelola course']);
        $role = Role::query()->create(['slug' => 'trainer', 'name' => 'Trainer / Mentor']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
