<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LearningPathAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyLearningPathController extends Controller
{
    public function index(Request $request): Response
    {
        $assignments = LearningPathAssignment::query()->where('user_id', $request->user()->id)->with('learningPath.pathCourses.course:id,code,title,prerequisite_course_id,is_mandatory')->latest()->paginate(12);
        $courseIds = $assignments->getCollection()->flatMap(fn ($a) => $a->learningPath->pathCourses->pluck('course_id'))->unique();
        $enrollments = Enrollment::query()->where('user_id', $request->user()->id)->whereIn('course_id', $courseIds)->whereIn('status', ['active', 'pending_review', 'completed', 'not_passed'])->get()->keyBy('course_id');
        $assignments->getCollection()->transform(function ($assignment) use ($enrollments): array {
            $completed = 0;
            $previousMandatoryIncomplete = false;
            $courses = $assignment->learningPath->pathCourses->map(function ($pathCourse) use ($enrollments, &$completed, &$previousMandatoryIncomplete): array {
                $enrollment = $enrollments->get($pathCourse->course_id);
                $status = $enrollment?->status;
                $lockedByPrerequisite = $pathCourse->course?->prerequisite_course_id
                    && $enrollments->get($pathCourse->course->prerequisite_course_id)?->status !== 'completed';
                $lockedBySequence = $previousMandatoryIncomplete;

                if ($status === 'completed') {
                    $completed++;
                }

                if ($pathCourse->course?->is_mandatory && $status !== 'completed') {
                    $previousMandatoryIncomplete = true;
                }

                return [
                    'id' => $pathCourse->course_id,
                    'sequence' => $pathCourse->sequence,
                    'code' => $pathCourse->course?->code,
                    'title' => $pathCourse->course?->title,
                    'enrollment_status' => match ($status) {
                        'not_passed' => 'needs_guidance',
                        'pending_review' => 'pending_review',
                        default => $status,
                    },
                    'locked' => $lockedByPrerequisite || $lockedBySequence,
                    'lock_reason' => $lockedByPrerequisite
                        ? 'Selesaikan course prasyarat terlebih dahulu.'
                        : ($lockedBySequence ? 'Selesaikan course wajib sebelumnya terlebih dahulu.' : null),
                ];
            });

            return [
                'id' => $assignment->id,
                'name' => $assignment->learningPath->name,
                'description' => $assignment->learningPath->description,
                'progress' => ['completed' => $completed, 'total' => $courses->count()],
                'courses' => $courses,
            ];
        });

        return Inertia::render('learning/Paths', ['paths' => $assignments]);
    }
}
