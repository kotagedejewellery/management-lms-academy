<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonResource;
use App\Models\User;
use App\Support\ModuleLearningProgression;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
                        'transcript' => $lesson->transcript,
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
        $this->ensureDraft($course);
        $data = $this->moduleData($request, $course);

        DB::transaction(function () use ($course, $data): void {
            $lockedCourse = Course::query()->lockForUpdate()->findOrFail($course->id);
            $this->ensureDraft($lockedCourse);
            $lockedCourse->modules()->create([...$data, 'sequence' => $lockedCourse->modules()->max('sequence') + 1]);
        });

        return to_route('courses.content', $course);
    }

    public function updateModule(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $module->update([...$this->moduleData($request, $course, $module), 'sequence' => $module->sequence]);

        return to_route('courses.content', $course);
    }

    public function storeLesson(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $data = $this->lessonData($request, $module);
        $lesson = DB::transaction(function () use ($module, $request, $data): Lesson {
            $lockedModule = CourseModule::query()->lockForUpdate()->findOrFail($module->id);
            $lesson = $lockedModule->lessons()->create([...$data, 'sequence' => $lockedModule->lessons()->max('sequence') + 1]);
            $this->storeResource($request, $lesson);

            return $lesson;
        });

        return to_route('courses.content', $course);
    }

    public function updateLesson(Request $request, Course $course, CourseModule $module, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id, 404);
        $data = $this->lessonData($request, $module, $lesson);
        $resourcesToDelete = DB::transaction(function () use ($lesson, $request, $data): Collection {
            $lesson->update([...$data, 'sequence' => $lesson->sequence]);

            return $this->storeResource($request, $lesson);
        });
        $this->deleteResourceFiles($resourcesToDelete);

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
        DB::transaction(function () use ($course, $module): void {
            $sequence = $module->sequence;
            $module->delete();
            $course->modules()->where('sequence', '>', $sequence)->decrement('sequence');
        });
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

    public function moveModule(Request $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($course, $module, $direction): void {
            Course::query()->lockForUpdate()->findOrFail($course->id);
            $current = CourseModule::query()->lockForUpdate()->findOrFail($module->id);
            $target = CourseModule::query()
                ->where('course_id', $course->id)
                ->when($direction === 'up', fn ($query) => $query->where('sequence', '<', $current->sequence)->orderByDesc('sequence'), fn ($query) => $query->where('sequence', '>', $current->sequence)->orderBy('sequence'))
                ->lockForUpdate()
                ->first();

            if (! $target) {
                return;
            }

            $this->swapSequences($current, $target);
        });

        return to_route('courses.content', $course);
    }

    public function moveLesson(Request $request, Course $course, CourseModule $module, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourses($request);
        $this->ensureDraft($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        abort_unless($lesson->module_id === $module->id, 404);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($module, $lesson, $direction): void {
            CourseModule::query()->lockForUpdate()->findOrFail($module->id);
            $current = Lesson::query()->lockForUpdate()->findOrFail($lesson->id);
            $target = Lesson::query()
                ->where('module_id', $module->id)
                ->when($direction === 'up', fn ($query) => $query->where('sequence', '<', $current->sequence)->orderByDesc('sequence'), fn ($query) => $query->where('sequence', '>', $current->sequence)->orderBy('sequence'))
                ->lockForUpdate()
                ->first();

            if (! $target) {
                return;
            }

            $this->swapSequences($current, $target);
        });

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
        abort_unless($lesson->module_id === $module->id && $resource->lesson_id === $lesson->id && in_array($resource->mime_type, ['application/pdf', 'video/mp4', 'video/webm'], true), 404);

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
        abort_if($course->status !== 'draft', 422, 'Materi hanya dapat diubah saat course masih draft.');
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
        ]);
    }

    /** @return array<string, mixed> */
    private function lessonData(Request $request, CourseModule $module, ?Lesson $lesson = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['article', 'video', 'pdf', 'presentation'])],
            'content' => ['nullable', 'string', 'max:60000'],
            'transcript' => ['nullable', 'string', 'max:60000'],
            'resource_url' => ['nullable', 'url', 'max:2048'],
            'resource' => ['nullable', 'file', 'max:51200', 'mimetypes:application/pdf,video/mp4,video/webm'],
            'replace_resource' => ['nullable', 'boolean'],
            'is_required' => ['required', 'boolean'],
        ]);

        if ($data['type'] === 'article' && blank($data['content'])) {
            throw ValidationException::withMessages(['content' => 'Isi artikel wajib diisi.']);
        }

        if ($lesson && $data['type'] !== $lesson->type) {
            throw ValidationException::withMessages(['type' => 'Tipe materi tidak dapat diubah setelah lesson dibuat.']);
        }

        $hasExistingSource = $lesson && $lesson->resources()
            ->whereIn('mime_type', $this->acceptedResourceMimes($data['type']))
            ->exists();
        if (in_array($data['type'], ['video', 'pdf'], true) && blank($data['resource_url'] ?? null) && ! $request->hasFile('resource') && ! $hasExistingSource) {
            throw ValidationException::withMessages(['resource_url' => 'Masukkan URL atau unggah file resource untuk video atau PDF.']);
        }

        if ($data['type'] === 'presentation' && ! $this->isCanvaViewUrl($data['resource_url'] ?? null)) {
            throw ValidationException::withMessages(['resource_url' => 'Gunakan tautan Canva yang dapat dilihat (view atau embed), bukan tautan editor.']);
        }

        if ($request->hasFile('resource') && ! $this->resourceMatchesLessonType($data['type'], $request->file('resource')->getMimeType())) {
            throw ValidationException::withMessages(['resource' => 'Jenis file harus sesuai dengan tipe lesson yang dipilih.']);
        }

        if ($lesson && $lesson->resources()->whereNotIn('mime_type', $this->acceptedResourceMimes($data['type']))->exists()) {
            throw ValidationException::withMessages(['type' => 'Hapus resource yang tidak sesuai sebelum mengubah tipe lesson.']);
        }

        return Arr::except($data, ['resource', 'replace_resource']);
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

        $requiredPreTest = $course->assessments()->whereNull('module_id')->where('type', 'pre_test')->where('is_required', true)->first();
        abort_unless(! $requiredPreTest || $requiredPreTest->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists(), 403);

        $lesson = $request->route('lesson');
        if ($lesson instanceof Lesson) {
            $module = $lesson->module;
            abort_unless($module && app(ModuleLearningProgression::class)->canAccessLesson($course, $enrollment, $module), 403);
        }
    }

    /** @return Collection<int, LessonResource> */
    private function storeResource(Request $request, Lesson $lesson): Collection
    {
        if (! $request->hasFile('resource')) {
            return collect();
        }

        $file = $request->file('resource');
        $disk = config('filesystems.default');
        $path = $file->store('lesson-resources/'.$lesson->id, $disk);
        if ($path === false) {
            throw new RuntimeException('Resource lesson tidak dapat disimpan.');
        }

        try {
            $resource = $lesson->resources()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);

            if (! $request->boolean('replace_resource')) {
                return collect();
            }

            $replacedResources = $lesson->resources()
                ->where('id', '!=', $resource->id)
                ->whereIn('mime_type', $this->acceptedResourceMimes($lesson->type))
                ->get();
            $lesson->resources()->whereKey($replacedResources->modelKeys())->delete();

            return $replacedResources;
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    private function resourceMatchesLessonType(string $lessonType, ?string $mimeType): bool
    {
        return in_array($mimeType, $this->acceptedResourceMimes($lessonType), true);
    }

    private function isCanvaViewUrl(?string $resourceUrl): bool
    {
        if (blank($resourceUrl)) {
            return false;
        }

        $host = strtolower((string) parse_url($resourceUrl, PHP_URL_HOST));
        $path = trim(strtolower((string) parse_url($resourceUrl, PHP_URL_PATH)), '/');

        if ($host === 'canva.link') {
            return $path !== '';
        }

        if (! in_array($host, ['canva.com', 'www.canva.com'], true)) {
            return false;
        }

        $segments = explode('/', $path);
        if ($segments[0] !== 'design' || in_array('edit', $segments, true)) {
            return false;
        }

        parse_str((string) parse_url($resourceUrl, PHP_URL_QUERY), $query);

        return in_array('view', $segments, true) || array_key_exists('embed', $query);
    }

    private function swapSequences(Model $current, Model $target): void
    {
        $currentSequence = (int) $current->getAttribute('sequence');
        $targetSequence = (int) $target->getAttribute('sequence');
        $current->update(['sequence' => 0]);
        $target->update(['sequence' => $currentSequence]);
        $current->update(['sequence' => $targetSequence]);
    }

    /** @return list<string> */
    private function acceptedResourceMimes(string $lessonType): array
    {
        return match ($lessonType) {
            'pdf' => ['application/pdf'],
            'video' => ['video/mp4', 'video/webm'],
            default => [],
        };
    }

    /** @param iterable<LessonResource> $resources */
    private function deleteResourceFiles(iterable $resources): void
    {
        foreach ($resources as $resource) {
            Storage::disk($resource->disk)->delete($resource->path);
        }
    }
}
