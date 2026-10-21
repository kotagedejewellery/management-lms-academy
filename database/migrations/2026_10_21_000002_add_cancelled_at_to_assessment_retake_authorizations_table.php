<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_retake_authorizations', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable()->after('used_at');
            $table->index(['assessment_id', 'enrollment_id', 'used_at', 'cancelled_at'], 'assessment_retake_authorizations_available_index');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_retake_authorizations', function (Blueprint $table): void {
            $table->dropIndex('assessment_retake_authorizations_available_index');
            $table->dropColumn('cancelled_at');
        });
    }
};
