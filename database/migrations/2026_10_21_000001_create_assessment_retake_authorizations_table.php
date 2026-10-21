<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_retake_authorizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['assessment_id', 'enrollment_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_retake_authorizations');
    }
};
