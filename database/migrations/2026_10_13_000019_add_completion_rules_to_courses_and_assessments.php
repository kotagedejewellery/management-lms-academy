<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->boolean('requires_post_test')->default(false)->after('is_mandatory');
        });

        Schema::table('assessments', function (Blueprint $table): void {
            $table->boolean('is_required')->default(false)->after('type');
        });

        DB::table('courses')->whereExists(function ($query): void {
            $query->selectRaw('1')
                ->from('assessments')
                ->whereColumn('assessments.course_id', 'courses.id')
                ->where('assessments.type', 'post_test');
        })->update(['requires_post_test' => true]);
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropColumn('is_required');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('requires_post_test');
        });
    }
};
