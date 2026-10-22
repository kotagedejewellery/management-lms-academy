<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class ModuleLearningProgression
{
    /** @return Collection<int, array{unlocked: bool, materials_unlocked: bool, post_available: bool, completed: bool}> */
    public function states(Course $course, Enrollment $enrollment): Collection
    {
        $modules = $course->modules()->with(['lessons:id,module_id,is_required', 'assessments:id,module_id,type'])->orderBy('sequence')->get();
        $completedLessons = $enrollment->lessonProgress()->where('status', 'completed')->pluck('lesson_id')->flip();
        $assessmentIds = $modules->flatMap->assessments->pluck('id');
        $attempts = AssessmentAttempt::query()
            ->where('enrollment_id', $enrollment->id)
            ->whereNotNull('submitted_at')
            ->whereIn('assessment_id', $assessmentIds)
            ->get(['assessment_id', 'passed']);
        $states = collect();
        $previousCompleted = true;

        foreach ($modules as $module) {
            $preTest = $module->assessments->firstWhere('type', 'pre_test');
            $postTest = $module->assessments->firstWhere('type', 'post_test');
            $preCompleted = ! $preTest || $attempts->contains('assessment_id', $preTest->id);
            $requiredLessons = $module->lessons->where('is_required', true);
            $lessonsCompleted = $requiredLessons->every(fn ($lesson) => $completedLessons->has($lesson->id));
            $postSubmitted = ! $postTest || $attempts->contains('assessment_id', $postTest->id);
            $materialsUnlocked = $previousCompleted && $preCompleted;
            $completed = $materialsUnlocked && $lessonsCompleted && $postSubmitted;

            $states->put($module->id, [
                'unlocked' => $previousCompleted,
                'materials_unlocked' => $materialsUnlocked,
                'post_available' => $materialsUnlocked && $lessonsCompleted,
                'completed' => $completed,
            ]);
            $previousCompleted = $completed;
        }

        return $states;
    }

    public function canAccessLesson(Course $course, Enrollment $enrollment, CourseModule $module): bool
    {
        return $this->coursePreTestCompleted($course, $enrollment)
            && ($this->states($course, $enrollment)->get($module->id)['materials_unlocked'] ?? false);
    }

    public function canStartAssessment(Assessment $assessment, Course $course, Enrollment $enrollment): bool
    {
        if ($assessment->module_id === null) {
            return $assessment->type === 'pre_test'
                || ($this->requiredCourseLessonsCompleted($course, $enrollment)
                    && $this->courseModulesCompleted($course, $enrollment));
        }

        $state = $this->states($course, $enrollment)->get($assessment->module_id);

        return $assessment->type === 'pre_test'
            ? ($state['unlocked'] ?? false)
            : ($state['post_available'] ?? false);
    }

    public function courseModulesCompleted(Course $course, Enrollment $enrollment): bool
    {
        return $this->states($course, $enrollment)->every(fn (array $state) => $state['completed']);
    }

    public function courseReadyForReview(Course $course, Enrollment $enrollment): bool
    {
        if (! $this->courseModulesCompleted($course, $enrollment)) {
            return false;
        }

        if (! $course->requires_post_test) {
            return true;
        }

        $postTest = $course->assessments()
            ->whereNull('module_id')
            ->where('type', 'post_test')
            ->first();

        return $postTest !== null && $postTest->attempts()
            ->where('enrollment_id', $enrollment->id)
            ->whereNotNull('submitted_at')
            ->exists();
    }

    private function coursePreTestCompleted(Course $course, Enrollment $enrollment): bool
    {
        $preTest = $course->assessments()->whereNull('module_id')->where('type', 'pre_test')->where('is_required', true)->first();

        return ! $preTest || $preTest->attempts()->where('enrollment_id', $enrollment->id)->whereNotNull('submitted_at')->exists();
    }

    private function requiredCourseLessonsCompleted(Course $course, Enrollment $enrollment): bool
    {
        $requiredLessonIds = $course->lessons()->where('is_required', true)->pluck('lessons.id');

        return $requiredLessonIds->isEmpty() || $enrollment->lessonProgress()
            ->where('status', 'completed')
            ->whereIn('lesson_id', $requiredLessonIds)
            ->count() === $requiredLessonIds->count();
    }
}
