<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalLearningCatalogResetSeeder extends Seeder
{
    use ResetsOperationalData;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('Reset katalog pembelajaran hanya dapat dijalankan pada environment local atau testing.');

            return;
        }

        DB::transaction(function (): void {
            $this->clearOperationalData();
            $this->call(LocalUserRoleSeeder::class);
            app(CustomerServiceDealmakerSeeder::class)->seedCourse(includePresentations: false);
        });
    }
}
