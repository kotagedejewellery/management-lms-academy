<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSixAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => app(CustomerServiceDealmakerSeeder::class)->seedModuleSixAssessments());
    }
}
