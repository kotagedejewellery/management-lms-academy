<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_private_lesson_resource(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'SOP Penjualan',
            'type' => 'pdf',
            'is_required' => true,
            'sequence' => 1,
            'resource' => UploadedFile::fake()->create('sop-penjualan.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

        $resource = \DB::table('lesson_resources')->first();

        $this->assertNotNull($resource);
        Storage::disk('local')->assertExists($resource->path);
    }

    public function test_admin_cannot_upload_a_pdf_for_a_video_lesson(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'Video produk',
            'type' => 'video',
            'is_required' => true,
            'sequence' => 1,
            'resource' => UploadedFile::fake()->create('panduan.pdf', 200, 'application/pdf'),
        ])->assertSessionHasErrors('resource');
    }

    public function test_admin_can_add_a_canva_view_presentation(): void
    {
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'Presentasi layanan pelanggan',
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/view?embed',
            'is_required' => true,
            'sequence' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/view?embed',
        ]);
    }

    public function test_admin_cannot_add_a_canva_editor_link_as_a_presentation(): void
    {
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'Presentasi layanan pelanggan',
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/edit',
            'is_required' => true,
            'sequence' => 1,
        ])->assertSessionHasErrors('resource_url');
    }

    public function test_admin_cannot_add_a_canva_link_that_is_neither_viewable_nor_embeddable(): void
    {
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'Presentasi layanan pelanggan',
            'type' => 'presentation',
            'resource_url' => 'https://www.canva.com/design/DAHJVwit-10/presentasi-layanan/comment',
            'is_required' => true,
            'sequence' => 1,
        ])->assertSessionHasErrors('resource_url');
    }

    public function test_admin_can_add_a_canva_short_link_presentation(): void
    {
        $admin = $this->courseAdmin();
        $module = $this->module($admin);

        $this->actingAs($admin)->post(route('courses.lessons.store', ['course' => $module->course_id, 'module' => $module->id]), [
            'title' => 'Presentasi singkat',
            'type' => 'presentation',
            'resource_url' => 'https://canva.link/89g2ztdcfb7og9q',
            'is_required' => true,
            'sequence' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'type' => 'presentation',
            'resource_url' => 'https://canva.link/89g2ztdcfb7og9q',
        ]);
    }

    public function test_admin_can_edit_a_media_lesson_without_reuploading_its_existing_source(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $module = $this->module($admin);
        $lesson = $module->lessons()->create(['title' => 'SOP awal', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs($admin)->put(route('courses.lessons.update', [$module->course, $module, $lesson]), [
            'title' => 'SOP terbaru',
            'type' => 'pdf',
            'is_required' => true,
            'sequence' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'title' => 'SOP terbaru']);
        Storage::disk('local')->assertExists($path);
    }

    public function test_admin_can_replace_a_media_lesson_source(): void
    {
        Storage::fake('local');
        $admin = $this->courseAdmin();
        $module = $this->module($admin);
        $lesson = $module->lessons()->create(['title' => 'SOP', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $oldPath = 'lesson-resources/'.$lesson->id.'/sop-lama.pdf';
        Storage::disk('local')->put($oldPath, 'old resource');
        $lesson->resources()->create(['disk' => 'local', 'path' => $oldPath, 'original_name' => 'sop-lama.pdf', 'mime_type' => 'application/pdf', 'size' => 12]);

        $this->actingAs($admin)->put(route('courses.lessons.update', [$module->course, $module, $lesson]), [
            'title' => 'SOP',
            'type' => 'pdf',
            'is_required' => true,
            'sequence' => 1,
            'replace_resource' => true,
            'resource' => UploadedFile::fake()->create('sop-baru.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseCount('lesson_resources', 1);
        $this->assertDatabaseHas('lesson_resources', ['lesson_id' => $lesson->id, 'original_name' => 'sop-baru.pdf']);
        Storage::disk('local')->assertMissing($oldPath);
    }

    public function test_admin_cannot_change_a_lesson_type_after_it_is_created(): void
    {
        $admin = $this->courseAdmin();
        $module = $this->module($admin);
        $lesson = $module->lessons()->create(['title' => 'Artikel', 'type' => 'article', 'content' => 'Isi', 'is_required' => true, 'sequence' => 1]);

        $this->actingAs($admin)->put(route('courses.lessons.update', [$module->course, $module, $lesson]), [
            'title' => 'Artikel',
            'type' => 'pdf',
            'is_required' => true,
            'sequence' => 1,
            'resource_url' => 'https://example.test/sop.pdf',
        ])->assertSessionHasErrors('type');
    }

    public function test_employee_cannot_download_private_lesson_resource(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'published']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.resources.download', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertForbidden();
    }

    public function test_enrolled_employee_can_preview_a_private_pdf(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'published']);
        $employee = User::factory()->create();
        $employee->enrollments()->create(['course_id' => $module->course_id, 'status' => 'active']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs($employee)
            ->get(route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertOk();

        $this->actingAs($employee)
            ->get(route('my-learning.show', ['course' => $module->course_id, 'lesson' => $lesson->id]))
            ->assertInertia(fn (Assert $page) => $page->where(
                'selected_lesson.resource_url',
                route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]),
            ));
    }

    public function test_enrolled_employee_can_preview_a_private_video(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'published']);
        $employee = User::factory()->create();
        $employee->enrollments()->create(['course_id' => $module->course_id, 'status' => 'active']);
        $lesson = $module->lessons()->create(['title' => 'Video produk', 'type' => 'video', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/produk.mp4';
        Storage::disk('local')->put($path, 'private video');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'produk.mp4', 'mime_type' => 'video/mp4', 'size' => 13]);

        $this->actingAs($employee)
            ->get(route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertOk();
    }

    public function test_enrolled_employee_cannot_access_an_archived_course_resource(): void
    {
        Storage::fake('local');
        $module = $this->module();
        $module->course->update(['status' => 'archived']);
        $employee = User::factory()->create();
        $employee->enrollments()->create(['course_id' => $module->course_id, 'status' => 'active']);
        $lesson = $module->lessons()->create(['title' => 'SOP Penjualan', 'type' => 'pdf', 'is_required' => true, 'sequence' => 1]);
        $path = 'lesson-resources/'.$lesson->id.'/sop-penjualan.pdf';
        Storage::disk('local')->put($path, 'private resource');
        $resource = $lesson->resources()->create(['disk' => 'local', 'path' => $path, 'original_name' => 'sop-penjualan.pdf', 'mime_type' => 'application/pdf', 'size' => 16]);

        $this->actingAs($employee)
            ->get(route('courses.resources.preview', [$module->course_id, $module->id, $lesson->id, $resource->id]))
            ->assertNotFound();
    }

    private function module(?User $trainer = null): CourseModule
    {
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'ONB-001', 'title' => 'Pengenalan KGJ', 'passing_grade' => 70]);
        if ($trainer) {
            $course->trainers()->attach($trainer);
        }

        return $course->modules()->create(['title' => 'Mulai di KGJ', 'sequence' => 1]);
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
