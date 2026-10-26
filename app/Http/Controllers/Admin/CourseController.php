<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeCourses($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $courses = Course::query()
            ->select(['id', 'category_id', 'division_id', 'position_id', 'prerequisite_course_id', 'code', 'title', 'thumbnail_path', 'estimated_minutes', 'is_mandatory', 'requires_post_test', 'certificate_enabled', 'passing_grade', 'status', 'updated_at'])
            ->with(['category:id,name', 'division:id,name', 'position:id,name', 'prerequisite:id,title'])
            ->withCount('modules')
            ->when(! $request->user()->hasPermission('manage-enrollments'), fn ($query) => $query->whereHas('trainers', fn ($query) => $query->whereKey($request->user()->id)))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Course $course) => $this->courseData($course));

        return Inertia::render('admin/courses/Index', [
            'courses' => $courses,
            'filters' => $filters,
            'can_create_course' => $request->user()->hasPermission('manage-enrollments'),
            'is_trainer' => $request->user()->hasRole('trainer') && ! $request->user()->hasPermission('manage-enrollments'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeCourses($request, requiresEnrollmentPermission: true);

        return Inertia::render('admin/courses/Create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCourses($request, requiresEnrollmentPermission: true);
        $data = $this->validatedData($request);
        $trainerIds = Arr::pull($data, 'trainer_ids');
        Arr::pull($data, 'trainer_ids_submitted');
        $thumbnail = Arr::pull($data, 'thumbnail');
        Arr::pull($data, 'remove_thumbnail');
        $this->ensureOrganizationTarget($data);
        $this->ensureDraft($data);
        $course = Course::query()->create($data);
        $this->storeThumbnail($course, $thumbnail);
        $this->syncTrainers($request, $course, $trainerIds);

        return to_route('courses.content', $course);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorizeCourses($request);

        return Inertia::render('admin/courses/Edit', [
            ...$this->formOptions($course),
            'course' => $this->courseData($course->load(['category:id,name', 'division:id,name', 'position:id,name', 'prerequisite:id,title', 'trainers:id'])),
            'publication_readiness' => $this->publicationReadiness($course),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourses($request);
        $data = $this->validatedData($request, $course);
        $trainerIds = Arr::pull($data, 'trainer_ids');
        Arr::pull($data, 'trainer_ids_submitted');
        $trainerIdsSubmitted = $request->boolean('trainer_ids_submitted');
        $thumbnail = Arr::pull($data, 'thumbnail');
        $removeThumbnail = (bool) Arr::pull($data, 'remove_thumbnail');
        $this->ensureOrganizationTarget($data);
        $this->ensurePrerequisiteDoesNotLoop($course, $data['prerequisite_course_id'] ?? null);

        if ($data['status'] === 'published') {
            if ($message = $this->publicationReadinessError($course, $data['requires_post_test'])) {
                throw ValidationException::withMessages(['status' => $message]);
            }
        }

        $data['published_at'] = $data['status'] === 'published' ? ($course->published_at ?? now()) : null;
        $course->update($data);
        if ($thumbnail) {
            $this->storeThumbnail($course, $thumbnail);
        } elseif ($removeThumbnail) {
            $this->removeThumbnail($course);
        }
        $this->syncTrainers(
            $request,
            $course,
            $trainerIdsSubmitted && $trainerIds === null ? [] : $trainerIds,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => match ($data['status']) {
                'published' => 'Course berhasil dipublikasikan.',
                'archived' => 'Course berhasil diarsipkan.',
                default => 'Course berhasil disimpan sebagai draf.',
            },
        ]);

        return to_route('courses.content', $course);
    }

    public function preview(Request $request, Course $course): Response
    {
        $this->authorizeCourses($request);
        $course->load(['category:id,name', 'division:id,name', 'position:id,name', 'trainers:id,name', 'modules.lessons:id,module_id,title,type,is_required,sequence']);

        return Inertia::render('admin/courses/Preview', [
            'course' => [
                ...$this->courseData($course),
                'trainers' => $course->trainers->map->only(['id', 'name'])->values(),
                'modules' => $course->modules->map(fn ($module) => [
                    ...$module->only(['id', 'title', 'sequence']),
                    'lessons' => $module->lessons->map->only(['id', 'title', 'type', 'is_required', 'sequence'])->values(),
                ])->values(),
            ],
        ]);
    }

    public function thumbnail(Request $request, Course $course): StreamedResponse
    {
        $path = $course->thumbnail_path;
        abort_unless(is_string($path), 404);

        $user = $request->user();
        $canManage = $user?->canManageCourse($course);
        $isEligibleLearner = $course->status === 'published'
            && (! $course->division_id || $course->division_id === $user?->division_id)
            && (! $course->position_id || $course->position_id === $user?->position_id);
        abort_unless($canManage || $isEligibleLearner, 403);

        return Storage::disk($course->thumbnail_disk ?: config('filesystems.default'))->response(
            $path,
            null,
            array_filter([
                'Content-Type' => $course->thumbnail_mime,
                'Cache-Control' => 'private, max-age=86400, immutable',
            ]),
        );
    }

    private function authorizeCourses(Request $request, bool $requiresEnrollmentPermission = false): void
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('manage-courses'), 403);
        abort_if($requiresEnrollmentPermission && ! $user->hasPermission('manage-enrollments'), 403);

        $course = $request->route('course');
        if ($course instanceof Course) {
            abort_unless($user->canManageCourse($course), 403);
        }
    }

    /** @param array<string, mixed> $data */
    private function ensureDraft(array $data): void
    {
        if ($data['status'] === 'published') {
            throw ValidationException::withMessages(['status' => 'Buat course sebagai draf, lalu tambahkan lesson sebelum menerbitkannya.']);
        }
    }

    /** @param array<string, mixed> $data */
    private function ensureOrganizationTarget(array $data): void
    {
        if (! ($data['position_id'] ?? null)) {
            return;
        }

        $positionMatchesDivision = ($data['division_id'] ?? null)
            && Position::query()
                ->whereKey($data['position_id'])
                ->where('division_id', $data['division_id'])
                ->exists();

        if (! $positionMatchesDivision) {
            throw ValidationException::withMessages(['position_id' => 'Jabatan target harus berada pada divisi target yang dipilih.']);
        }
    }

    private function ensurePrerequisiteDoesNotLoop(Course $course, ?int $prerequisiteCourseId): void
    {
        $seen = [];
        $currentId = $prerequisiteCourseId;

        while ($currentId) {
            if ($currentId === $course->id || isset($seen[$currentId])) {
                throw ValidationException::withMessages(['prerequisite_course_id' => 'Prasyarat course tidak boleh membentuk siklus.']);
            }

            $seen[$currentId] = true;
            $currentId = Course::query()->whereKey($currentId)->value('prerequisite_course_id');
        }
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('course_categories', 'id')],
            'division_id' => ['nullable', 'integer', Rule::exists('divisions', 'id')],
            'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')],
            'prerequisite_course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')],
            'code' => ['required', 'string', 'max:32', Rule::unique('courses', 'code')->ignore($course)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'dimensions:ratio=16/9', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_mandatory' => ['required', 'boolean'],
            'requires_post_test' => ['nullable', 'boolean'],
            'certificate_enabled' => ['nullable', 'boolean'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'trainer_ids' => ['nullable', 'array'],
            'trainer_ids.*' => ['integer', Rule::exists('users', 'id')],
            'trainer_ids_submitted' => ['nullable', 'boolean'],
        ], [
            'thumbnail.image' => 'Pilih file gambar untuk thumbnail course.',
            'thumbnail.mimes' => 'Gunakan gambar berformat JPG, PNG, WebP, atau AVIF.',
            'thumbnail.dimensions' => 'Gunakan gambar dengan rasio 16:9 agar tampil rapi di katalog.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2 MB.',
        ]);

        $postTest = $course?->assessments()->whereNull('module_id')->where('type', 'post_test')->first();
        $data['requires_post_test'] = $postTest !== null || ($data['requires_post_test'] ?? ($course !== null && $course->requires_post_test));
        $data['certificate_enabled'] = (bool) ($data['certificate_enabled'] ?? ($course !== null && $course->certificate_enabled));
        if ($postTest) {
            $data['passing_grade'] = $postTest->passing_grade;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function formOptions(?Course $course = null): array
    {
        $canAssignTrainers = request()->user()?->hasPermission('manage-enrollments') ?? false;

        return [
            'categories' => CourseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'prerequisites' => Course::query()->whereKeyNot($course?->id)->orderBy('title')->get(['id', 'title']),
            'trainers' => $canAssignTrainers ? User::query()->where('status', 'active')->whereHas('roles', fn ($query) => $query->where('slug', 'trainer'))->orderBy('name')->get(['id', 'name']) : [],
            'can_assign_trainers' => $canAssignTrainers,
        ];
    }

    /** @return array<string, mixed> */
    private function courseData(Course $course): array
    {
        return [
            'id' => $course->id,
            'code' => $course->code,
            'title' => $course->title,
            'description' => $course->description,
            'thumbnail_url' => $course->thumbnail_path ? route('courses.thumbnail', ['course' => $course, 'v' => $course->updated_at?->getTimestamp()]) : null,
            'estimated_minutes' => $course->estimated_minutes,
            'is_mandatory' => $course->is_mandatory,
            'requires_post_test' => $course->requires_post_test,
            'certificate_enabled' => $course->certificate_enabled,
            'passing_grade' => $course->passing_grade,
            'status' => $course->status,
            'category' => $course->category?->only(['id', 'name']),
            'division' => $course->division?->only(['id', 'name']),
            'position' => $course->position?->only(['id', 'name']),
            'prerequisite' => $course->prerequisite?->only(['id', 'title']),
            'modules_count' => $course->modules_count ?? $course->modules()->count(),
            'trainer_ids' => $course->relationLoaded('trainers') ? $course->trainers->pluck('id')->values() : [],
        ];
    }

    /** @return array{ready: bool, checks: list<array{ready: bool, label: string, message: string, href: string|null}>} */
    private function publicationReadiness(Course $course, ?bool $requiresCoursePostTest = null): array
    {
        $requiresCoursePostTest ??= $course->requires_post_test;
        $checks = [];
        $checks[] = $course->lessons()->exists()
            ? ['ready' => true, 'label' => 'Materi course', 'message' => 'Minimal satu materi tersedia.', 'href' => route('courses.content', $course)]
            : ['ready' => false, 'label' => 'Materi course', 'message' => 'Tambahkan minimal satu materi sebelum menerbitkan course.', 'href' => route('courses.content', $course)];

        if ($checks[0]['ready']) {
            $checks[] = $this->contentReady($course)
                ? ['ready' => true, 'label' => 'Sumber materi', 'message' => 'Setiap materi memiliki sumber yang valid.', 'href' => route('courses.content', $course)]
                : ['ready' => false, 'label' => 'Sumber materi', 'message' => 'Lengkapi sumber untuk setiap materi video atau PDF sebelum menerbitkan course.', 'href' => route('courses.content', $course)];
        }

        $assessments = $course->assessments()
            ->with(['module:id,title', 'questions.answers'])
            ->where(function ($query): void {
                $query->whereNotNull('module_id')
                    ->orWhere('type', 'post_test')
                    ->orWhere(fn ($query) => $query->where('type', 'pre_test')->where('is_required', true));
            })
            ->get();

        if ($requiresCoursePostTest && ! $assessments->contains(fn (Assessment $assessment): bool => $assessment->module_id === null && $assessment->type === 'post_test')) {
            $checks[] = [
                'ready' => false,
                'label' => 'Post-Test course',
                'message' => 'Tambahkan Post-Test course beserta soal dan jawaban benar sebelum menerbitkan course evaluatif.',
                'href' => route('courses.assessments.index', $course),
            ];
        }

        foreach ($assessments as $assessment) {
            $label = $this->assessmentLabel($assessment);
            $href = route('courses.assessments.index', array_filter([
                'course' => $course,
                'module' => $assessment->module_id,
            ]));
            if ($assessment->questions->isEmpty()) {
                $checks[] = ['ready' => false, 'label' => $label, 'message' => "{$label} belum memiliki soal.", 'href' => $href];

                continue;
            }
            if ($question = $assessment->questions->first(fn ($question): bool => $question->answers->where('is_correct', true)->count() !== 1)) {
                $checks[] = [
                    'ready' => false,
                    'label' => $label,
                    'message' => "Soal {$question->sequence} pada {$label} harus memiliki tepat satu jawaban benar.",
                    'href' => route('courses.assessments.index', array_filter([
                        'course' => $course,
                        'module' => $assessment->module_id,
                        'question' => $question->id,
                    ])),
                ];

                continue;
            }

            $checks[] = ['ready' => true, 'label' => $label, 'message' => "{$label} siap dipublikasikan.", 'href' => $href];
        }

        return [
            'ready' => collect($checks)->every(fn (array $check): bool => $check['ready']),
            'checks' => $checks,
        ];
    }

    private function publicationReadinessError(Course $course, bool $requiresCoursePostTest): ?string
    {
        $check = collect($this->publicationReadiness($course, $requiresCoursePostTest)['checks'])
            ->firstWhere('ready', false);

        return $check['message'] ?? null;
    }

    private function assessmentLabel(Assessment $assessment): string
    {
        $typeLabel = $assessment->type === 'pre_test' ? 'Pre-Test' : 'Post-Test';

        return $assessment->module_id
            ? $typeLabel.' modul '.$assessment->module?->title
            : $typeLabel.' course';
    }

    private function contentReady(Course $course): bool
    {
        $course->loadMissing('modules.lessons.resources:id,lesson_id,mime_type');

        return ! $course->modules->flatMap->lessons->contains(function ($lesson): bool {
            if ($lesson->type === 'article') {
                return blank($lesson->content);
            }

            if (filled($lesson->resource_url)) {
                return false;
            }

            $acceptedMimes = match ($lesson->type) {
                'pdf' => ['application/pdf'],
                'video' => ['video/mp4', 'video/webm'],
                default => [],
            };

            return $acceptedMimes === [] || ! $lesson->resources->contains(fn ($resource): bool => in_array($resource->mime_type, $acceptedMimes, true));
        });
    }

    private function storeThumbnail(Course $course, ?UploadedFile $thumbnail): void
    {
        if (! $thumbnail) {
            return;
        }

        $disk = config('filesystems.default');
        [$path, $mimeType] = $this->storeOptimizedThumbnail($thumbnail, $disk);

        $previousPath = $course->thumbnail_path;
        $previousDisk = $course->thumbnail_disk ?: $disk;

        try {
            $course->update([
                'thumbnail_path' => $path,
                'thumbnail_disk' => $disk,
                'thumbnail_mime' => $mimeType,
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        if ($previousPath) {
            Storage::disk($previousDisk)->delete($previousPath);
        }
    }

    private function removeThumbnail(Course $course): void
    {
        $path = $course->thumbnail_path;
        if (! $path) {
            return;
        }

        $disk = $course->thumbnail_disk ?: config('filesystems.default');
        $course->update([
            'thumbnail_path' => null,
            'thumbnail_disk' => null,
            'thumbnail_mime' => null,
        ]);

        Storage::disk($disk)->delete($path);
    }

    /** @return array{0: string, 1: string|null} */
    private function storeOptimizedThumbnail(UploadedFile $thumbnail, string $disk): array
    {
        $mimeType = $thumbnail->getMimeType();

        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true) && function_exists('imagewebp')) {
            $source = @imagecreatefromstring((string) $thumbnail->get());

            if ($source !== false) {
                $width = min(1280, imagesx($source));
                $height = max(1, (int) round($width * 9 / 16));
                $optimized = imagecreatetruecolor($width, $height);
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);
                $transparent = imagecolorallocatealpha($optimized, 0, 0, 0, 127);
                if ($transparent !== false) {
                    imagefill($optimized, 0, 0, $transparent);
                }
                imagecopyresampled($optimized, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

                ob_start();
                $encoded = imagewebp($optimized, null, 82);
                $contents = ob_get_clean();
                imagedestroy($optimized);
                imagedestroy($source);

                if ($encoded && is_string($contents)) {
                    $path = 'course-thumbnails/'.Str::uuid().'.webp';

                    if (Storage::disk($disk)->put($path, $contents)) {
                        return [$path, 'image/webp'];
                    }
                }
            }
        }

        $path = $thumbnail->store('course-thumbnails', $disk);
        if ($path === false) {
            throw new RuntimeException('Thumbnail course tidak dapat disimpan.');
        }

        return [$path, $mimeType];
    }

    /** @param array<int, int>|null $trainerIds */
    private function syncTrainers(Request $request, Course $course, ?array $trainerIds): void
    {
        if (! $request->user()->hasPermission('manage-enrollments')) {
            abort_if($trainerIds !== null, 403);
            $course->trainers()->syncWithoutDetaching([$request->user()->id]);

            return;
        }

        if ($trainerIds === null) {
            return;
        }

        $eligibleCount = User::query()
            ->whereIn('id', $trainerIds)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'trainer'))
            ->count();
        if ($eligibleCount !== count($trainerIds)) {
            throw ValidationException::withMessages(['trainer_ids' => 'Pilih hanya pengguna dengan role Trainer / Mentor.']);
        }

        $course->trainers()->sync($trainerIds);
    }
}
