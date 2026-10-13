<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LearningController extends Controller
{
    public function index(Request $request): Response
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->whereHas('course', fn ($query) => $query->where('status', 'published'))
            ->with(['course:id,code,title,description,estimated_minutes'])
            ->withCount([
                'lessonProgress as completed_lessons_count' => fn ($query) => $query->where('status', 'completed'),
                'course as lessons_count' => fn ($query) => $query->withCount('lessons'),
            ])
            ->orderByRaw('due_at is null, due_at')
            ->paginate(12)
            ->through(fn (Enrollment $enrollment) => [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'due_at' => $enrollment->due_at?->toDateString(),
                'completed_lessons_count' => $enrollment->completed_lessons_count,
                'lessons_count' => $enrollment->lessons_count,
                'course' => $enrollment->course?->only(['id', 'code', 'title', 'description', 'estimated_minutes']),
            ]);

        return Inertia::render('learning/Index', ['enrollments' => $enrollments]);
    }

    public function show(Request $request, Course $course): Response
    {
        $enrollment = $this->enrollment($request, $course);
        $course->load([
            'category:id,name',
            'division:id,name',
            'position:id,name',
            'modules.lessons' => fn ($query) => $query->select(['id', 'module_id', 'title', 'type', 'is_required', 'sequence']),
            'assessments' => fn ($query) => $query
                ->select(['id', 'course_id', 'type', 'is_required', 'attempt_limit'])
                ->withCount('questions')
                ->with([
                    'attempts' => fn ($attempts) => $attempts
                        ->where('enrollment_id', $enrollment->id)
                        ->whereNotNull('submitted_at')
                        ->orderByDesc('attempt_number'),
                ]),
        ]);
        $completed = $enrollment->lessonProgress()->where('status', 'completed')->pluck('lesson_id')->all();
        $requiredLessonIds = $course->lessons()->where('is_required', true)->pluck('lessons.id')->all();
        $completedRequiredLessonCount = count(array_intersect($requiredLessonIds, $completed));
        $postTestReady = $requiredLessonIds === [] || $completedRequiredLessonCount === count($requiredLessonIds);
        $totalLessonCount = $course->modules->sum(fn ($module) => $module->lessons->count());
        $lessons = $course->modules->flatMap->lessons;
        $requestedLessonId = $request->integer('lesson');
        $selectedSummary = $requestedLessonId ? $lessons->firstWhere('id', $requestedLessonId) : null;
        abort_if($request->has('lesson') && ! $selectedSummary, 404);
        $selectedSummary ??= $lessons->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completed, true)) ?? $lessons->first();
        $requiredPreTest = $course->assessments->first(fn ($assessment) => $assessment->type === 'pre_test' && $assessment->is_required);
        $preTestCompleted = ! $requiredPreTest || $requiredPreTest->attempts->isNotEmpty();
        $selectedLesson = $preTestCompleted && $selectedSummary
            ? Lesson::query()->with('resources:id,lesson_id,original_name,mime_type')->findOrFail($selectedSummary->id)
            : null;
        $selectedModule = $selectedLesson ? $course->modules->firstWhere('id', $selectedLesson->module_id) : null;

        return Inertia::render('learning/Show', [
            'enrollment' => ['id' => $enrollment->id, 'status' => $enrollment->status, 'due_at' => $enrollment->due_at?->toDateString()],
            'course' => [
                ...$course->only(['id', 'code', 'title', 'description', 'estimated_minutes', 'requires_post_test']),
                'metadata' => [
                    'category' => $course->category?->name,
                    'target_audience' => collect([$course->division?->name, $course->position?->name])->filter()->join(' · ') ?: 'Seluruh karyawan',
                ],
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
                'modules' => $course->modules->map(function ($module) use ($completed, $selectedSummary) {
                    $moduleCompleted = $module->lessons->every(fn (Lesson $lesson) => in_array($lesson->id, $completed, true));
                    $moduleInProgress = ! $moduleCompleted && ($module->lessons->contains(fn (Lesson $lesson) => in_array($lesson->id, $completed, true)) || $module->lessons->contains('id', $selectedSummary?->id));

                    return [
                        ...$module->only(['id', 'title', 'sequence']),
                        'status' => $moduleCompleted ? 'completed' : ($moduleInProgress ? 'in_progress' : 'not_started'),
                        'lessons' => $module->lessons->map(fn (Lesson $lesson) => [
                            ...$lesson->only(['id', 'title', 'type', 'is_required', 'sequence']),
                            'completed' => in_array($lesson->id, $completed, true),
                            'status' => in_array($lesson->id, $completed, true) ? 'completed' : ($selectedSummary?->id === $lesson->id ? 'in_progress' : 'not_started'),
                        ]),
                    ];
                }),
            ],
            'selected_lesson' => $selectedLesson ? [
                ...$selectedLesson->only(['id', 'module_id', 'title', 'type', 'content', 'transcript', 'resource_url', 'is_required', 'sequence']),
                'module_title' => $selectedModule?->title,
                'module_sequence' => $selectedModule?->sequence,
                'completed' => in_array($selectedLesson->id, $completed, true),
                'resources' => $selectedLesson->resources->map(fn ($resource) => $resource->only(['id', 'original_name', 'mime_type'])),
            ] : null,
        ]);
    }

    public function completeLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->enrollment($request, $course);
        abort_unless($enrollment->status === 'active', 404);
        abort_unless($lesson->module()->where('course_id', $course->id)->exists(), 404);
        $preTest = $course->assessments()->where('type', 'pre_test')->where('is_required', true)->first();
        if ($preTest && ! $preTest->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists()) {
            throw ValidationException::withMessages(['pre_test' => 'Selesaikan Pre-Test wajib sebelum memulai materi.']);
        }

        $enrollment->lessonProgress()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            ['status' => 'completed', 'completed_at' => now()],
        );
        $enrollment->update(['started_at' => $enrollment->started_at ?? now()]);

        if (! $course->requires_post_test && $this->requiredLessonsCompleted($course, $enrollment)) {
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
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
}
