<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->index(['status', 'created_at']);
            $table->index(['course_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['course_id', 'status', 'created_at']);
        });
    }
};
