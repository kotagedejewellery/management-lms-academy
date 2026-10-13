<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('attempt_limit');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('passed');
            $table->timestamp('expires_at')->nullable()->after('started_at');
            $table->timestamp('submitted_at')->nullable()->change();
            $table->decimal('score', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'expires_at']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('time_limit_minutes');
        });
    }
};
