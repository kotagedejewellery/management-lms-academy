<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductionCustomerServiceDealmakerForceRefreshSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['production', 'testing'])) {
            throw new RuntimeException('Force refresh katalog Customer Service hanya dapat dijalankan di production.');
        }

        DB::transaction(function (): void {
            $course = Course::query()->where('code', 'CS-DEAL-001')->first();

            if ($course) {
                $this->clearCourseLearningHistory($course);
                CourseModule::query()->where('course_id', $course->id)->delete();
            }

            app(CustomerServiceDealmakerSeeder::class)->seedCourse(includePresentations: false);
        });
    }

    private function clearCourseLearningHistory(Course $course): void
    {
        $assessmentIds = Assessment::query()->where('course_id', $course->id)->select('id');
        $lessonIds = Lesson::query()
            ->whereIn('module_id', CourseModule::query()->where('course_id', $course->id)->select('id'))
            ->select('id');
        $attemptIds = DB::table('assessment_attempts')->whereIn('assessment_id', $assessmentIds)->select('id');

        DB::table('assessment_responses')->whereIn('assessment_attempt_id', $attemptIds)->delete();
        DB::table('assessment_drafts')->whereIn('assessment_id', $assessmentIds)->delete();
        DB::table('assessment_retake_authorizations')->whereIn('assessment_id', $assessmentIds)->delete();
        DB::table('assessment_attempts')->whereIn('assessment_id', $assessmentIds)->delete();
        DB::table('lesson_progress')->whereIn('lesson_id', $lessonIds)->delete();
        DB::table('certificates')->where('course_id', $course->id)->delete();

        Enrollment::query()
            ->where('course_id', $course->id)
            ->update(['status' => 'active', 'started_at' => null, 'completed_at' => null]);
    }
}
