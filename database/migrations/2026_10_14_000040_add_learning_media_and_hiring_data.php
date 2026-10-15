<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('thumbnail_path')->nullable()->after('description');
            $table->string('thumbnail_disk')->nullable()->after('thumbnail_path');
            $table->string('thumbnail_mime', 100)->nullable()->after('thumbnail_disk');
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->unsignedInteger('playback_seconds')->default(0)->after('status');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->date('hired_at')->nullable()->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('hired_at');
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->dropColumn('playback_seconds');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn(['thumbnail_path', 'thumbnail_disk', 'thumbnail_mime']);
        });
    }
};
