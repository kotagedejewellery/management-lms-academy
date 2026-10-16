<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncLearningPath;
use App\Models\Course;
use App\Models\LearningPath;
use App\Models\LearningPathCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LearningPathContentController extends Controller
{
    public function index(Request $request, LearningPath $learningPath): Response
    {
        $this->authorizePaths($request);
        $learningPath->load('pathCourses.course:id,code,title,status');

        return Inertia::render('admin/learning-paths/Content', [
            'learningPath' => [
                ...$learningPath->only(['id', 'name', 'description', 'status']),
                'courses' => $learningPath->pathCourses->map(fn (LearningPathCourse $pathCourse) => [
                    'id' => $pathCourse->id,
                    'sequence' => $pathCourse->sequence,
                    'course' => $pathCourse->course?->only(['id', 'code', 'title', 'status']),
                ]),
            ],
            'courses' => Course::query()->where('status', 'published')->orderBy('title')->get(['id', 'code', 'title']),
        ]);
    }

    public function store(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $this->authorizePaths($request);
        $this->ensureMutable($learningPath);
        $data = $this->validatedData($request, $learningPath);
        $course = Course::query()->findOrFail((int) $data['course_id']);

        if ($course->status !== 'published') {
            throw ValidationException::withMessages(['course_id' => 'Hanya course yang sudah dipublikasikan dapat masuk ke learning path.']);
        }

        DB::transaction(function () use ($learningPath, $data): void {
            $lockedLearningPath = LearningPath::query()->lockForUpdate()->findOrFail($learningPath->id);
            $this->ensureMutable($lockedLearningPath);

            $count = $lockedLearningPath->pathCourses()->count();
            $lockedLearningPath->pathCourses()->create([...$data, 'sequence' => $count + 1]);
        });

        if ($learningPath->status === 'published') {
            SyncLearningPath::dispatch($learningPath->id);
        }

        return to_route('learning-paths.content', $learningPath);
    }

    public function destroy(Request $request, LearningPath $learningPath, LearningPathCourse $pathCourse): RedirectResponse
    {
        $this->authorizePaths($request);
        abort_if($learningPath->status !== 'draft', 422, 'Course hanya dapat dihapus dari learning path yang masih draft.');
        abort_unless($pathCourse->learning_path_id === $learningPath->id, 404);
        abort_if($learningPath->assignments()->exists(), 422, 'Learning path yang sudah memiliki assignment tidak dapat diubah.');
        $sequence = $pathCourse->sequence;
        $pathCourse->delete();
        $learningPath->pathCourses()->where('sequence', '>', $sequence)->decrement('sequence');

        return to_route('learning-paths.content', $learningPath);
    }

    public function move(Request $request, LearningPath $learningPath, LearningPathCourse $pathCourse): RedirectResponse
    {
        $this->authorizePaths($request);
        $this->ensureMutable($learningPath);
        abort_unless($pathCourse->learning_path_id === $learningPath->id, 404);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($learningPath, $pathCourse, $direction): void {
            LearningPath::query()->lockForUpdate()->findOrFail($learningPath->id);
            $current = LearningPathCourse::query()->lockForUpdate()->findOrFail($pathCourse->id);
            $target = LearningPathCourse::query()
                ->where('learning_path_id', $learningPath->id)
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

        return to_route('learning-paths.content', $learningPath);
    }

    private function authorizePaths(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-learning-paths'), 403);
    }

    private function ensureMutable(LearningPath $learningPath): void
    {
        abort_if($learningPath->status === 'archived', 422, 'Learning path yang diarsipkan tidak dapat diubah.');
    }

    /** @return array<string, int> */
    private function validatedData(Request $request, LearningPath $learningPath): array
    {
        return $request->validate([
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id'), Rule::unique('learning_path_courses', 'course_id')->where('learning_path_id', $learningPath->id)],
        ]);
    }
}
