<?php

namespace App\Http\Controllers;

use App\Jobs\IssueCertificate;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Support\LearningAvailability;
use App\Support\ModuleLearningProgression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LearningController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'state' => ['nullable', Rule::in(['not_started', 'in_progress', 'completed', 'not_passed'])],
            'mandatory' => ['nullable', Rule::in(['mandatory', 'optional'])],
        ]);

        $enrollments = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->whereHas('course', fn ($query) => $query->where('status', 'published'))
            ->with(['course:id,code,title,description,estimated_minutes,is_mandatory,prerequisite_course_id'])
            ->when($filters['mandatory'] ?? null, fn ($query, string $mandatory) => $query->whereHas('course', fn ($course) => $course->where('is_mandatory', $mandatory === 'mandatory')))
            ->when($filters['state'] ?? null, function ($query, string $state): void {
                match ($state) {
                    'not_started' => $query->where('status', 'active')->whereNull('started_at'),
                    'in_progress' => $query->where('status', 'active')->whereNotNull('started_at'),
                    default => $query->where('status', $state),
                };
            })
            ->withCount([
                'lessonProgress as completed_lessons_count' => fn ($query) => $query->where('status', 'completed'),
                'course as lessons_count' => fn ($query) => $query->withCount('lessons'),
            ])
            ->orderByRaw('due_at is null, due_at')
            ->paginate(12)
            ->withQueryString();
        $allEnrollmentStatuses = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->get(['course_id', 'status']);
        $availability = app(LearningAvailability::class)->for(
            $request->user(),
            $enrollments->getCollection()->pluck('course')->filter(),
            $allEnrollmentStatuses,
        );
        $enrollments->getCollection()->transform(fn (Enrollment $enrollment) => [
            'id' => $enrollment->id,
            'status' => $enrollment->status,
            'due_at' => $enrollment->due_at?->toDateString(),
            'completed_lessons_count' => $enrollment->completed_lessons_count,
            'lessons_count' => $enrollment->lessons_count,
            'course' => $enrollment->course?->only(['id', 'code', 'title', 'description', 'estimated_minutes', 'is_mandatory']),
            'locked' => $availability[$enrollment->course_id]['locked'] ?? false,
            'lock_reason' => $availability[$enrollment->course_id]['reason'] ?? null,
        ]);

        return Inertia::render('learning/Index', ['enrollments' => $enrollments, 'filters' => $filters]);
    }

    public function show(Request $request, Course $course): Response
    {
        $enrollment = $this->enrollment($request, $course);
        $course->load([
            'category:id,name',
            'division:id,name',
            'position:id,name',
            'trainers:id,name',
            'modules.lessons' => fn ($query) => $query->select(['id', 'module_id', 'title', 'type', 'is_required', 'sequence']),
            'modules.assessments' => fn ($query) => $query
                ->withCount('questions')
                ->with([
                    'attempts' => fn ($attempts) => $attempts
                        ->where('enrollment_id', $enrollment->id)
                        ->whereNotNull('submitted_at')
                        ->orderByDesc('attempt_number'),
                ]),
            'assessments' => fn ($query) => $query
                ->whereNull('module_id')
                ->select(['id', 'course_id', 'type', 'is_required', 'attempt_limit'])
                ->withCount('questions')
                ->with([
                    'attempts' => fn ($attempts) => $attempts
                        ->where('enrollment_id', $enrollment->id)
                        ->whereNotNull('submitted_at')
                        ->orderByDesc('attempt_number'),
                ]),
        ]);
        $lessonProgress = $enrollment->lessonProgress()->get()->keyBy('lesson_id');
        $completed = $lessonProgress->where('status', 'completed')->keys()->all();
        $requiredLessonIds = $course->lessons()->where('is_required', true)->pluck('lessons.id')->all();
        $completedRequiredLessonCount = count(array_intersect($requiredLessonIds, $completed));
        $postTestReady = $requiredLessonIds === [] || $completedRequiredLessonCount === count($requiredLessonIds);
        $totalLessonCount = $course->modules->sum(fn ($module) => $module->lessons->count());
        $moduleStates = app(ModuleLearningProgression::class)->states($course, $enrollment);
        /** @var Collection<int, Lesson> $lessons */
        $lessons = $course->modules->flatMap(fn (CourseModule $module) => $module->lessons);
        $requiredPreTest = $course->assessments->first(fn ($assessment) => $assessment->type === 'pre_test' && $assessment->is_required);
        $preTestCompleted = ! $requiredPreTest || $requiredPreTest->attempts->isNotEmpty();
        $availableLessons = $lessons->filter(fn (Lesson $lesson) => $preTestCompleted && ($moduleStates->get($lesson->module_id)['materials_unlocked'] ?? false));
        $requestedLessonId = $request->integer('lesson');
        $selectedSummary = $requestedLessonId ? $lessons->firstWhere('id', $requestedLessonId) : null;
        abort_if($request->has('lesson') && ! $selectedSummary, 404);
        abort_if($selectedSummary && ! $availableLessons->contains('id', $selectedSummary->id), 403);
        $selectedSummary ??= $availableLessons->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completed, true)) ?? $availableLessons->first();
        $selectedLesson = $selectedSummary
            ? Lesson::query()->with('resources:id,lesson_id,original_name,mime_type')->findOrFail($selectedSummary->id)
            : null;
        $selectedModule = $selectedLesson ? $course->modules->firstWhere('id', $selectedLesson->module_id) : null;
        $selectedResource = $selectedLesson?->resources->first(fn ($resource) => match ($selectedLesson->type) {
            'pdf' => $resource->mime_type === 'application/pdf',
            'video' => in_array($resource->mime_type, ['video/mp4', 'video/webm'], true),
            default => false,
        });
        $selectedResourceUrl = $selectedLesson->resource_url
            ?? ($selectedResource && $selectedModule
                ? route('courses.resources.preview', [$course, $selectedModule, $selectedLesson, $selectedResource])
                : null);
        $playbackSeconds = $selectedLesson && $lessonProgress->has($selectedLesson->id)
            ? (int) $lessonProgress->get($selectedLesson->id)->playback_seconds
            : 0;

        return Inertia::render('learning/Show', [
            'enrollment' => ['id' => $enrollment->id, 'status' => $enrollment->status, 'due_at' => $enrollment->due_at?->toDateString()],
            'course' => [
                ...$course->only(['id', 'code', 'title', 'description', 'estimated_minutes', 'requires_post_test']),
                'metadata' => [
                    'category' => $course->category?->name,
                    'target_audience' => collect([$course->division?->name, $course->position?->name])->filter()->join(' · ') ?: 'Seluruh karyawan',
                ],
                'mentors' => $course->trainers->map->only(['id', 'name'])->values(),
                'progress' => [
                    'completed_lessons' => count($completed),
                    'total_lessons' => $totalLessonCount,
                    'completed_required_lessons' => $completedRequiredLessonCount,
                    'required_lessons' => count($requiredLessonIds),
                ],
                'assessments' => $course->assessments->map(function ($assessment) use ($postTestReady) {
                    $attempts = $assessment->attempts;
                    $latestAttempt = $attempts->first();

                    return [
                        ...$assessment->only(['id', 'type', 'is_required', 'attempt_limit']),
                        'ready' => $assessment->questions_count > 0 && ($assessment->type === 'pre_test' || $postTestReady),
                        'attempts_used' => $attempts->count(),
                        'best_score' => $attempts->max('score'),
                        'latest_attempt' => $latestAttempt?->only(['attempt_number', 'score', 'passed']),
                    ];
                }),
                'pre_test_required' => $requiredPreTest !== null,
                'pre_test_completed' => $preTestCompleted,
                'modules' => $course->modules->map(function ($module) use ($completed, $selectedSummary, $moduleStates, $preTestCompleted) {
                    $state = $moduleStates->get($module->id);
                    $moduleCompleted = $state['completed'] ?? false;
                    $moduleInProgress = ! $moduleCompleted && ($module->lessons->contains(fn (Lesson $lesson) => in_array($lesson->id, $completed, true)) || $module->lessons->contains('id', $selectedSummary?->id));
                    $moduleRequiredLessons = $module->lessons->where('is_required', true);
                    $modulePostReady = $moduleRequiredLessons->isEmpty() || $moduleRequiredLessons->every(fn (Lesson $lesson) => in_array($lesson->id, $completed, true));

                    return [
                        ...$module->only(['id', 'title', 'sequence']),
                        'status' => $moduleCompleted ? 'completed' : (! $preTestCompleted || ! ($state['materials_unlocked'] ?? false) ? 'locked' : ($moduleInProgress ? 'in_progress' : 'not_started')),
                        'locked' => ! $preTestCompleted || ! ($state['materials_unlocked'] ?? false),
                        'lock_reason' => ! $preTestCompleted
                            ? 'Selesaikan Pre-Test course terlebih dahulu.'
                            : (! ($state['unlocked'] ?? false) ? 'Selesaikan Post-Test modul sebelumnya terlebih dahulu.' : 'Selesaikan Pre-Test modul ini terlebih dahulu.'),
                        'assessments' => $module->assessments->map(function ($assessment) use ($modulePostReady, $state) {
                            $attempts = $assessment->attempts;
                            $latestAttempt = $attempts->first();

                            return [
                                ...$assessment->only(['id', 'module_id', 'type', 'is_required', 'attempt_limit']),
                                'ready' => $assessment->questions_count > 0 && ($assessment->type === 'pre_test' ? ($state['unlocked'] ?? false) : ($state['post_available'] ?? $modulePostReady)),
                                'attempts_used' => $attempts->count(),
                                'best_score' => $attempts->max('score'),
                                'latest_attempt' => $latestAttempt?->only(['attempt_number', 'score', 'passed']),
                            ];
                        })->values(),
                        'lessons' => $module->lessons->map(fn (Lesson $lesson) => [
                            ...$lesson->only(['id', 'title', 'type', 'is_required', 'sequence']),
                            'completed' => in_array($lesson->id, $completed, true),
                            'locked' => ! $preTestCompleted || ! ($state['materials_unlocked'] ?? false),
                            'status' => in_array($lesson->id, $completed, true) ? 'completed' : ($selectedSummary?->id === $lesson->id ? 'in_progress' : 'not_started'),
                        ]),
                    ];
                }),
            ],
            'selected_lesson' => $selectedLesson ? [
                ...$selectedLesson->only(['id', 'module_id', 'title', 'type', 'content', 'transcript', 'resource_url', 'is_required', 'sequence']),
                'resource_url' => $selectedResourceUrl,
                'module_title' => $selectedModule?->title,
                'module_sequence' => $selectedModule?->sequence,
                'completed' => in_array($selectedLesson->id, $completed, true),
                'playback_seconds' => $playbackSeconds,
                'resources' => $selectedLesson->resources->map(fn ($resource) => $resource->only(['id', 'original_name', 'mime_type'])),
            ] : null,
        ]);
    }

    public function savePlayback(Request $request, Course $course, Lesson $lesson): HttpResponse
    {
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
        abort_unless($lesson->type === 'video' && $lesson->module()->where('course_id', $course->id)->exists(), 404);

        $this->ensureLessonAvailable($course, $enrollment, $lesson);

        $seconds = $request->validate(['seconds' => ['required', 'integer', 'min:0', 'max:604800']])['seconds'];
        $progress = $enrollment->lessonProgress()->firstOrNew(['lesson_id' => $lesson->id]);
        $progress->status ??= 'in_progress';
        $progress->playback_seconds = $seconds;
        $progress->save();
        $enrollment->update(['started_at' => $enrollment->started_at ?? now()]);

        return response()->noContent();
    }

    public function completeLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
        abort_unless($lesson->module()->where('course_id', $course->id)->exists(), 404);
        $this->ensureLessonAvailable($course, $enrollment, $lesson);

        $enrollment->lessonProgress()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            ['status' => 'completed', 'completed_at' => now()],
        );
        $enrollment->update(['started_at' => $enrollment->started_at ?? now()]);

        if (! $course->requires_post_test && app(ModuleLearningProgression::class)->courseModulesCompleted($course, $enrollment)) {
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
            if ($course->certificate_enabled) {
                IssueCertificate::dispatch($enrollment->id);
            }
        }

        return to_route('my-learning.show', ['course' => $course, 'lesson' => $lesson]);
    }

    private function enrollment(Request $request, Course $course): Enrollment
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->whereHas('course', fn ($query) => $query->where('status', 'published'))
            ->firstOrFail();

        abort_unless($course->prerequisiteCompletedBy($request->user()), 403);

        return $enrollment;
    }

    private function requiredLessonsCompleted(Course $course, Enrollment $enrollment): bool
    {
        $requiredLessonIds = $course->lessons()->where('is_required', true)->pluck('lessons.id');

        return $requiredLessonIds->isEmpty() || $enrollment->lessonProgress()
            ->where('status', 'completed')
            ->whereIn('lesson_id', $requiredLessonIds)
            ->count() === $requiredLessonIds->count();
    }

    private function ensureLessonAvailable(Course $course, Enrollment $enrollment, Lesson $lesson): void
    {
        $coursePreTest = $course->assessments()->whereNull('module_id')->where('type', 'pre_test')->where('is_required', true)->first();
        if ($coursePreTest && ! $coursePreTest->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists()) {
            throw ValidationException::withMessages(['pre_test' => 'Selesaikan Pre-Test wajib sebelum memulai materi.']);
        }

        $module = $lesson->module;
        if (! $module || ! app(ModuleLearningProgression::class)->canAccessLesson($course, $enrollment, $module)) {
            throw ValidationException::withMessages(['module' => 'Selesaikan Pre-Test atau modul sebelumnya sebelum membuka materi ini.']);
        }
    }
}
