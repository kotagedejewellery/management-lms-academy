<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalLearningCatalogResetSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('Reset katalog pembelajaran hanya dapat dijalankan pada environment local atau testing.');

            return;
        }

        DB::transaction(function (): void {
            $this->clearLearningData();

            app(CustomerServiceDealmakerSeeder::class)->seedCourse(includePresentations: false);
        });
    }

    private function clearLearningData(): void
    {
        DB::table('assessment_retake_authorizations')->delete();
        DB::table('assessment_responses')->delete();
        DB::table('assessment_drafts')->delete();
        DB::table('assessment_attempts')->delete();
        DB::table('certificates')->delete();
        DB::table('lesson_progress')->delete();
        DB::table('lesson_resources')->delete();
        DB::table('course_trainers')->delete();
        DB::table('learning_path_rules')->delete();
        DB::table('learning_path_assignments')->delete();
        DB::table('learning_path_courses')->delete();
        DB::table('learning_paths')->delete();
        DB::table('answers')->delete();
        DB::table('questions')->delete();
        DB::table('assessments')->delete();
        DB::table('lessons')->delete();
        DB::table('modules')->delete();
        DB::table('enrollments')->delete();
        DB::table('courses')->delete();
        DB::table('course_categories')->delete();
    }
}
