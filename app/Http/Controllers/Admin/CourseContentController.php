<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseContentController extends Controller
{
    public function index(Request $request, Course $course): Response
    {
        $this->authorizeCourses($request);
        $course->load(['modules.lessons.resources']);

        return Inertia::render('admin/courses/Content', [
            'course' => [
                'id' => $course->id,
                'code' => $course->code,
                'title' => $course->title,
                'status' => $course->status,
                'modules' => $course->modules->map(fn (CourseModule $module) => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'sequence' => $module->sequence,
                    'lessons' => $module->lessons->map(fn (Lesson $lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'type' => $lesson->type,
                        'content' => $lesson->content,
                        'resource_url' => $lesson->resource_url,
                        'resources' => $lesson->resources->map(fn (LessonResource $resource) => $resource->only(['id', 'original_name', 'mime_type', 'size'])),
                        'is_required' => $lesson->is_required,
                        'sequence' => $lesson->sequence,
                    ]),
                ]),
            ],
        ]);
    }

    public function storeModule(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourses($request);
        $course->modules()->create($this->moduleData($request, $course));

        return to_route('courses.content', $course);
    }

    public function updateModule(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureModuleBelongsToCourse($course, $module);
        $module->update($this->moduleData($request, $course, $module));

        return to_route('courses.content', $course);
    }

    public function storeLesson(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureModuleBelongsToCourse($course, $module);
        $lesson = $module->lessons()->create($this->lessonData($request, $module));
        $this->storeResource($request, $lesson);

        return to_route('courses.content', $course);
    }

    public function updateLesson(Request $request, Course $course, CourseModule $module, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id, 404);
        $lesson->update($this->lessonData($request, $module, $lesson));
        $this->storeResource($request, $lesson);

        return to_route('courses.content', $course);
    }

    public function destroyModule(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $lessonIds = $module->lessons()->pluck('id');
        /** @var list<int> $lessonIdsList */
        $lessonIdsList = $lessonIds->map(fn (mixed $id): int => (int) $id)->values()->all();
        $this->ensureLessonsUnused($lessonIdsList);
        $resources = LessonResource::query()->whereIn('lesson_id', $lessonIds)->get();
        $module->delete();
        $this->deleteResourceFiles($resources);

        return to_route('courses.content', $course);
    }

    public function destroyLesson(Request $request, Course $course, CourseModule $module, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id, 404);
        $this->ensureLessonsUnused([$lesson->id]);
        $resources = $lesson->resources()->get();
        $sequence = $lesson->sequence;
        $lesson->delete();
        $module->lessons()->where('sequence', '>', $sequence)->decrement('sequence');
        $this->deleteResourceFiles($resources);

        return to_route('courses.content', $course);
    }

    public function destroyResource(Request $request, Course $course, CourseModule $module, Lesson $lesson, LessonResource $resource): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id && $resource->lesson_id === $lesson->id, 404);
        $resource->delete();
        $this->deleteResourceFiles(collect([$resource]));

        return to_route('courses.content', $course);
    }

    public function downloadResource(Request $request, Course $course, CourseModule $module, Lesson $lesson, LessonResource $resource): StreamedResponse
    {
        $this->authorizeResourceAccess($request, $course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id && $resource->lesson_id === $lesson->id, 404);

        return Storage::disk($resource->disk)->download($resource->path, $resource->original_name, ['Content-Type' => $resource->mime_type]);
    }

    public function previewResource(Request $request, Course $course, CourseModule $module, Lesson $lesson, LessonResource $resource): StreamedResponse
    {
        $this->authorizeResourceAccess($request, $course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id && $resource->lesson_id === $lesson->id && $resource->mime_type === 'application/pdf', 404);

        return Storage::disk($resource->disk)->response($resource->path, $resource->original_name, ['Content-Type' => $resource->mime_type]);
    }

    private function authorizeCourses(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasPermission('manage-courses'), 403);

        $course = $request->route('course');
        if ($course instanceof Course) {
            abort_unless($user->canManageCourse($course), 403);
        }
    }

    private function ensureModuleBelongsToCourse(Course $course, CourseModule $module): void
    {
        abort_unless($module->course_id === $course->id, 404);
    }

    private function ensureDraft(Course $course): void
    {
        abort_if($course->status !== 'draft', 422, 'Materi hanya dapat dihapus saat course masih draft.');
    }

    /** @param list<int> $lessonIds */
    private function ensureLessonsUnused(array $lessonIds): void
    {
        abort_if(LessonProgress::query()->whereIn('lesson_id', $lessonIds)->exists(), 422, 'Materi yang sudah memiliki riwayat belajar tidak dapat dihapus.');
    }

    /** @return array<string, mixed> */
    private function moduleData(Request $request, Course $course, ?CourseModule $module = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:1', Rule::unique('modules', 'sequence')->where('course_id', $course->id)->ignore($module)],
        ]);
    }

    /** @return array<string, mixed> */
    private function lessonData(Request $request, CourseModule $module, ?Lesson $lesson = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['article', 'video', 'pdf'])],
            'content' => ['nullable', 'string', 'max:60000'],
            'transcript' => ['nullable', 'string', 'max:60000'],
            'resource_url' => ['nullable', 'url', 'max:2048'],
            'resource' => ['nullable', 'file', 'max:51200', 'mimetypes:application/pdf,video/mp4,video/webm'],
            'is_required' => ['required', 'boolean'],
            'sequence' => ['required', 'integer', 'min:1', Rule::unique('lessons', 'sequence')->where('module_id', $module->id)->ignore($lesson)],
        ]);

        if ($data['type'] === 'article' && blank($data['content'])) {
            throw ValidationException::withMessages(['content' => 'Isi artikel wajib diisi.']);
        }

        if (in_array($data['type'], ['video', 'pdf'], true) && blank($data['resource_url'] ?? null) && ! $request->hasFile('resource')) {
            throw ValidationException::withMessages(['resource_url' => 'Masukkan URL atau unggah file resource untuk video atau PDF.']);
        }

        return Arr::except($data, 'resource');
    }

    private function authorizeResourceAccess(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if ($user->canManageCourse($course)) {
            return;
        }

        abort_unless($course->status === 'published', 404);

        $enrollment = $course->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->first();
        abort_unless($enrollment && $course->prerequisiteCompletedBy($user), 403);

        $requiredPreTest = $course->assessments()->where('type', 'pre_test')->where('is_required', true)->first();
        abort_unless(! $requiredPreTest || $requiredPreTest->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists(), 403);
    }

    private function storeResource(Request $request, Lesson $lesson): void
    {
        if (! $request->hasFile('resource')) {
            return;
        }

        $file = $request->file('resource');
        $disk = config('filesystems.default');
        $path = $file->store('lesson-resources/'.$lesson->id, $disk);

        abort_unless(is_string($path), 500);
        $lesson->resources()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);
    }

    /** @param iterable<LessonResource> $resources */
    private function deleteResourceFiles(iterable $resources): void
    {
        foreach ($resources as $resource) {
            Storage::disk($resource->disk)->delete($resource->path);
        }
    }
}
