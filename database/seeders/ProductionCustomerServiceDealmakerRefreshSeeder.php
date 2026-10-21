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

class ProductionCustomerServiceDealmakerRefreshSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['production', 'testing'])) {
            throw new RuntimeException('Refresh katalog Customer Service hanya dapat dijalankan di production.');
        }

        DB::transaction(function (): void {
            $course = Course::query()->where('code', 'CS-DEAL-001')->first();

            if ($course) {
                $this->ensureCourseHasNoLearnerHistory($course);

                CourseModule::query()->where('course_id', $course->id)->delete();
            }

            app(CustomerServiceDealmakerSeeder::class)->seedCourse(includePresentations: false);
        });
    }

    private function ensureCourseHasNoLearnerHistory(Course $course): void
    {
        $assessmentIds = Assessment::query()->where('course_id', $course->id)->select('id');
        $lessonIds = Lesson::query()
            ->whereIn('module_id', CourseModule::query()->where('course_id', $course->id)->select('id'))
            ->select('id');

        $hasLearnerHistory = Enrollment::query()
            ->where('course_id', $course->id)
            ->where(fn ($query) => $query->whereNotNull('started_at')->orWhereNotNull('completed_at'))
            ->exists()
            || DB::table('lesson_progress')->whereIn('lesson_id', $lessonIds)->exists()
            || DB::table('assessment_attempts')->whereIn('assessment_id', $assessmentIds)->exists()
            || DB::table('assessment_drafts')->whereIn('assessment_id', $assessmentIds)->exists()
            || DB::table('assessment_retake_authorizations')->whereIn('assessment_id', $assessmentIds)->exists()
            || DB::table('certificates')->where('course_id', $course->id)->exists();

        if ($hasLearnerHistory) {
            throw new RuntimeException('Refresh dibatalkan karena CS-DEAL-001 sudah memiliki riwayat belajar peserta.');
        }
    }
}
