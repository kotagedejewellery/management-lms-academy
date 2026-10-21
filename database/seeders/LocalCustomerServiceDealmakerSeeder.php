<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalCustomerServiceDealmakerSeeder extends Seeder
{
    public function run(): void
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true)) {
            $this->command->warn('Seeder materi Customer Service hanya dapat dijalankan pada environment local atau testing.');

            return;
        }

        DB::transaction(function (): void {
            $course = app(CustomerServiceDealmakerSeeder::class)->seedCourse();

            User::query()->where('email', 'trainer@kgj.test')->first()?->managedCourses()->syncWithoutDetaching([$course->id]);

            $learner = User::query()->where('email', 'anisa@kgj.test')->first();
            if ($learner) {
                Enrollment::query()->firstOrCreate(
                    ['user_id' => $learner->id, 'course_id' => $course->id],
                    ['status' => 'active', 'due_at' => now()->addDays(30), 'started_at' => null, 'completed_at' => null],
                );
            }
        });
    }
}
