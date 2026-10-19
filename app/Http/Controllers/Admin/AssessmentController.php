<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function index(Request $request, Course $course): Response
    {
        $this->authorizeCourses($request);
        $course->load('modules');
        $moduleId = $request->integer('module');
        $module = $moduleId ? $course->modules->firstWhere('id', $moduleId) : null;
        abort_if($moduleId && ! $module, 404);
        $assessments = Assessment::query()
            ->where('course_id', $course->id)
            ->when($module, fn ($query) => $query->where('module_id', $module->id), fn ($query) => $query->whereNull('module_id'))
            ->with('questions.answers')
            ->get()
            ->keyBy('type');

        return Inertia::render('admin/courses/Assessments', [
            'course' => $course->only(['id', 'code', 'title']),
            'modules' => $course->modules->map->only(['id', 'title', 'sequence'])->values(),
            'selected_module' => $module?->only(['id', 'title']),
            'assessments' => collect(['pre_test', 'post_test'])->mapWithKeys(fn (string $type) => [$type => $this->assessmentData($assessments->get($type))]),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourses($request);
        $data = $request->validate([
            'type' => ['required', Rule::in(['pre_test', 'post_test'])],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'attempt_limit' => ['nullable', 'integer', 'min:1', 'max:3'],
            'is_required' => ['nullable', 'boolean'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
            'module_id' => ['nullable', 'integer'],
        ]);

        $module = isset($data['module_id'])
            ? CourseModule::query()->where('course_id', $course->id)->whereKey($data['module_id'])->first()
            : null;
        if (isset($data['module_id']) && ! $module) {
            throw ValidationException::withMessages(['module_id' => 'Modul evaluasi tidak ditemukan pada course ini.']);
        }
        $duplicate = Assessment::query()
            ->where('course_id', $course->id)
            ->where('type', $data['type'])
            ->when($module, fn ($query) => $query->where('module_id', $module->id), fn ($query) => $query->whereNull('module_id'))
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['type' => 'Jenis assessment ini sudah tersedia pada scope yang dipilih.']);
        }

        $assessment = $course->assessments()->create([
            ...$data,
            'module_id' => $module?->id,
            'attempt_limit' => $data['attempt_limit'] ?? 3,
            'is_required' => $module !== null || ($data['type'] === 'pre_test' && ($data['is_required'] ?? false)),
        ]);
        $this->syncCoursePassingGrade($course, $assessment);

        return to_route('courses.assessments.index', ['course' => $course, 'module' => $module?->id]);
    }

    public function update(Request $request, Course $course, Assessment $assessment): RedirectResponse
    {
        $this->authorizeCourses($request);
        abort_unless($assessment->course_id === $course->id, 404);

        $data = $request->validate([
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'attempt_limit' => ['sometimes', 'integer', 'min:1', 'max:3'],
            'is_required' => ['sometimes', 'boolean'],
            'time_limit_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:180'],
        ]);
        if ($assessment->module_id !== null || $assessment->type !== 'pre_test') {
            unset($data['is_required']);
        }
        $assessment->update($data);
        $this->syncCoursePassingGrade($course, $assessment);

        return to_route('courses.assessments.index', ['course' => $course, 'module' => $assessment->module_id]);
    }

    public function storeQuestion(Request $request, Course $course, Assessment $assessment): RedirectResponse
    {
        $this->authorizeCourses($request);
        abort_unless($assessment->course_id === $course->id, 404);
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'answers' => ['required', 'array', 'min:2', 'max:6'],
            'answers.*' => ['required', 'string', 'max:2000'],
            'correct_answer' => ['required', 'integer', 'min:0'],
        ]);
        abort_unless(array_key_exists($data['correct_answer'], $data['answers']), 422);

        DB::transaction(function () use ($assessment, $data): void {
            $lockedAssessment = Assessment::query()->lockForUpdate()->findOrFail($assessment->id);
            $question = $lockedAssessment->questions()->create([
                'prompt' => $data['prompt'],
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'],
                'sequence' => $lockedAssessment->questions()->max('sequence') + 1,
            ]);
            foreach ($data['answers'] as $sequence => $content) {
                $question->answers()->create(['content' => $content, 'sequence' => $sequence + 1, 'is_correct' => $sequence === $data['correct_answer']]);
            }
        });

        return to_route('courses.assessments.index', ['course' => $course, 'module' => $assessment->module_id]);
    }

    public function destroyQuestion(Request $request, Course $course, Assessment $assessment, Question $question): RedirectResponse
    {
        $this->authorizeCourses($request);
        abort_unless($assessment->course_id === $course->id && $question->assessment_id === $assessment->id, 404);
        abort_if($assessment->attempts()->exists(), 422, 'Soal yang sudah memiliki riwayat pengerjaan tidak dapat dihapus.');
        DB::transaction(function () use ($assessment, $question): void {
            Assessment::query()->lockForUpdate()->findOrFail($assessment->id);
            $lockedQuestion = Question::query()->lockForUpdate()->findOrFail($question->id);
            $sequence = $lockedQuestion->sequence;
            $lockedQuestion->delete();
            $assessment->questions()->where('sequence', '>', $sequence)->decrement('sequence');
        });

        return to_route('courses.assessments.index', ['course' => $course, 'module' => $assessment->module_id]);
    }

    public function moveQuestion(Request $request, Course $course, Assessment $assessment, Question $question): RedirectResponse
    {
        $this->authorizeCourses($request);
        abort_unless($assessment->course_id === $course->id && $question->assessment_id === $assessment->id, 404);
        abort_if($assessment->attempts()->exists(), 422, 'Soal yang sudah memiliki riwayat pengerjaan tidak dapat diubah urutannya.');
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($assessment, $question, $direction): void {
            Assessment::query()->lockForUpdate()->findOrFail($assessment->id);
            $current = Question::query()->lockForUpdate()->findOrFail($question->id);
            $target = Question::query()
                ->where('assessment_id', $assessment->id)
                ->when($direction === 'up', fn ($query) => $query->where('sequence', '<', $current->sequence)->orderByDesc('sequence'), fn ($query) => $query->where('sequence', '>', $current->sequence)->orderBy('sequence'))
                ->lockForUpdate()
                ->first();

            if (! $target) {
                return;
            }

            $currentSequence = $current->sequence;
            $targetSequence = $target->sequence;
            $current->update(['sequence' => 0]);
            $target->update(['sequence' => $currentSequence]);
            $current->update(['sequence' => $targetSequence]);
        });

        return to_route('courses.assessments.index', ['course' => $course, 'module' => $assessment->module_id]);
    }

    private function authorizeCourses(Request $request): void
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('manage-courses'), 403);

        $course = $request->route('course');
        if ($course instanceof Course) {
            abort_unless($user->canManageCourse($course), 403);
        }
    }

    /** @return array<string, mixed>|null */
    private function assessmentData(?Assessment $assessment): ?array
    {
        return $assessment ? [
            ...$assessment->only(['id', 'module_id', 'type', 'is_required', 'passing_grade', 'attempt_limit', 'time_limit_minutes']),
            'questions' => $assessment->questions->map(fn ($question) => [...$question->only(['id', 'prompt', 'explanation', 'points', 'sequence']), 'answers' => $question->answers->map->only(['id', 'content', 'is_correct', 'sequence'])]),
        ] : null;
    }

    private function syncCoursePassingGrade(Course $course, Assessment $assessment): void
    {
        if ($assessment->module_id === null && $assessment->type === 'post_test') {
            $course->update(['passing_grade' => $assessment->passing_grade, 'requires_post_test' => true]);
        }
    }
}
