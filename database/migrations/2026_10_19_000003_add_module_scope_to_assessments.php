<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            $table->foreignId('module_id')->nullable()->after('course_id')->constrained('modules')->cascadeOnDelete();
            $table->dropUnique(['course_id', 'type']);
        });

        DB::statement('create unique index assessments_course_type_unique on assessments (course_id, type) where module_id is null');
        DB::statement('create unique index assessments_module_type_unique on assessments (module_id, type) where module_id is not null');
    }

    public function down(): void
    {
        DB::statement('drop index if exists assessments_module_type_unique');
        DB::statement('drop index if exists assessments_course_type_unique');

        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('module_id');
            $table->unique(['course_id', 'type']);
        });
    }
};
