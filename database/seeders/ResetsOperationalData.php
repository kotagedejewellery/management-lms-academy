<?php

namespace Database\Seeders;

use App\Models\LessonResource;
use App\Models\ReportExport;
use App\Models\UserImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ResetsOperationalData
{
    private function clearOperationalData(): void
    {
        $this->deleteOperationalFiles();

        DB::table('assessment_retake_authorizations')->delete();
        DB::table('assessment_responses')->delete();
        DB::table('assessment_drafts')->delete();
        DB::table('assessment_attempts')->delete();
        DB::table('certificates')->delete();
        DB::table('lesson_progress')->delete();
        DB::table('lesson_resources')->delete();
        DB::table('course_trainers')->delete();
        DB::table('answers')->delete();
        DB::table('questions')->delete();
        DB::table('assessments')->delete();
        DB::table('lessons')->delete();
        DB::table('modules')->delete();
        DB::table('enrollments')->delete();
        DB::table('learning_path_rules')->delete();
        DB::table('learning_path_assignments')->delete();
        DB::table('learning_path_courses')->delete();
        DB::table('learning_paths')->delete();
        DB::table('courses')->delete();
        DB::table('course_categories')->delete();

        DB::table('user_notifications')->delete();
        DB::table('announcements')->delete();
        DB::table('report_exports')->delete();
        DB::table('user_imports')->delete();
        DB::table('activity_logs')->delete();
        DB::table('password_reset_tokens')->delete();
        DB::table('sessions')->delete();
        DB::table('failed_jobs')->delete();
        DB::table('job_batches')->delete();
        DB::table('jobs')->delete();
        DB::table('cache_locks')->delete();
        DB::table('cache')->delete();
    }

    private function deleteOperationalFiles(): void
    {
        LessonResource::query()->each(fn (LessonResource $resource) => Storage::disk($resource->disk)->delete($resource->path));
        ReportExport::query()->whereNotNull('path')->each(fn (ReportExport $report) => Storage::disk('local')->delete($report->path));
        UserImport::query()->each(fn (UserImport $import) => Storage::disk('local')->delete($import->path));
    }
}
