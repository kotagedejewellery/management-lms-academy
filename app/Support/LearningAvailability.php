<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningPathAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class LearningAvailability
{
    /**
     * @param  Collection<int, Course>  $courses
     * @param  Collection<int, Enrollment>  $enrollments
     * @return array<int, array{locked: bool, reason: string|null}>
     */
    public function for(User $user, Collection $courses, Collection $enrollments): array
    {
        $completedCourseIds = $enrollments
            ->where('status', 'completed')
            ->pluck('course_id')
            ->flip();
        $availability = $courses
            ->mapWithKeys(fn (Course $course) => [$course->id => [
                'locked' => $course->prerequisite_course_id !== null && ! $completedCourseIds->has($course->prerequisite_course_id),
                'reason' => $course->prerequisite_course_id !== null && ! $completedCourseIds->has($course->prerequisite_course_id)
                    ? 'Selesaikan course prasyarat terlebih dahulu.'
                    : null,
            ]])
            ->all();

        if ($courses->isEmpty()) {
            return $availability;
        }

        $courseIds = $courses->pluck('id');
        $assignments = LearningPathAssignment::query()
            ->where('user_id', $user->id)
            ->with('learningPath.pathCourses.course:id,is_mandatory')
            ->get();

        foreach ($assignments as $assignment) {
            $previousMandatoryIncomplete = false;

            foreach ($assignment->learningPath->pathCourses as $pathCourse) {
                if ($courseIds->contains($pathCourse->course_id) && $previousMandatoryIncomplete) {
                    $availability[$pathCourse->course_id] = [
                        'locked' => true,
                        'reason' => 'Selesaikan course wajib sebelumnya terlebih dahulu.',
                    ];
                }

                if ($pathCourse->course?->is_mandatory && ! $completedCourseIds->has($pathCourse->course_id)) {
                    $previousMandatoryIncomplete = true;
                }
            }
        }

        return $availability;
    }
}
