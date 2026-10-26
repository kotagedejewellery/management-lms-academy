<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductionOperationalDataResetSeeder extends Seeder
{
    use ResetsOperationalData;

    public function run(): void
    {
        if (! app()->environment(['production', 'testing'])) {
            throw new RuntimeException('Reset data operasional hanya dapat dijalankan pada environment production.');
        }

        if (app()->environment('production') && getenv('KGJ_FULL_DATA_RESET_CONFIRMATION') !== 'delete-all-data-except-users') {
            throw new RuntimeException('Reset dibatalkan. Tetapkan KGJ_FULL_DATA_RESET_CONFIRMATION=delete-all-data-except-users untuk menjalankan reset sekali ini.');
        }

        DB::transaction(function (): void {
            $this->clearOperationalData();
            $this->call(RolePermissionSeeder::class);
            app(CustomerServiceDealmakerSeeder::class)->seedCourse(includePresentations: false);
        });
    }
}
