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
        $course = Course::query()->findOrFail($data['course_id']);

        if ($course->status !== 'published') {
            throw ValidationException::withMessages(['course_id' => 'Hanya course yang sudah dipublikasikan dapat masuk ke learning path.']);
        }

        DB::transaction(function () use ($learningPath, $data): void {
            $count = $learningPath->pathCourses()->count();

            if ($data['sequence'] <= $count) {
                $offset = $count + 1;
                $learningPath->pathCourses()->where('sequence', '>=', $data['sequence'])->increment('sequence', $offset);
                $learningPath->pathCourses()
                    ->where('sequence', '>=', $data['sequence'] + $offset)
                    ->update(['sequence' => DB::raw('sequence - '.$count)]);
            }

            $learningPath->pathCourses()->create($data);
        });

        if ($learningPath->status === 'published') {
            SyncLearningPath::dispatch($learningPath->id);
        }

        return to_route('learning-paths.content', $learningPath);
    }

    public function update(Request $request, LearningPath $learningPath, LearningPathCourse $pathCourse): RedirectResponse
    {
        $this->authorizePaths($request);
        $this->ensureMutable($learningPath);
        abort_unless($pathCourse->learning_path_id === $learningPath->id, 404);
        $data = $this->validatedData($request, $learningPath, $pathCourse);

        if ($data['sequence'] !== $pathCourse->sequence) {
            DB::transaction(function () use ($learningPath, $pathCourse, $data): void {
                $count = $learningPath->pathCourses()->count();
                $offset = $count + 1;
                $oldSequence = $pathCourse->sequence;
                $pathCourse->update(['sequence' => 0]);

                if ($data['sequence'] < $oldSequence) {
                    $this->shiftSequences($learningPath, $data['sequence'], $oldSequence - 1, $offset, -$count);
                } else {
                    $this->shiftSequences($learningPath, $oldSequence + 1, $data['sequence'], $offset, -($count + 2));
                }

                $pathCourse->update(['sequence' => $data['sequence']]);
            });
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

    private function authorizePaths(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-learning-paths'), 403);
    }

    private function ensureMutable(LearningPath $learningPath): void
    {
        abort_if($learningPath->status === 'archived', 422, 'Learning path yang diarsipkan tidak dapat diubah.');
    }

    private function validatedData(Request $request, LearningPath $learningPath, ?LearningPathCourse $pathCourse = null): array
    {
        if ($pathCourse) {
            return $request->validate([
                'course_id' => ['required', 'integer', Rule::in([$pathCourse->course_id])],
                'sequence' => ['required', 'integer', 'min:1', 'max:'.$learningPath->pathCourses()->count()],
            ]);
        }

        return $request->validate([
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id'), Rule::unique('learning_path_courses', 'course_id')->where('learning_path_id', $learningPath->id)->ignore($pathCourse)],
            'sequence' => ['required', 'integer', 'min:1', 'max:'.($learningPath->pathCourses()->count() + 1)],
        ]);
    }

    private function shiftSequences(LearningPath $learningPath, int $from, int $to, int $offset, int $adjustment): void
    {
        $courses = $learningPath->pathCourses()->whereBetween('sequence', [$from, $to]);
        $courses->increment('sequence', $offset);
        $learningPath->pathCourses()->whereBetween('sequence', [$from + $offset, $to + $offset])->update(['sequence' => DB::raw('sequence + ('.$adjustment.')')]);
    }
}
