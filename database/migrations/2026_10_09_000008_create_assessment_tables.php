<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->string('type', 16);
            $t->unsignedTinyInteger('passing_grade')->default(70);
            $t->unsignedTinyInteger('attempt_limit')->default(3);
            $t->timestamps();
            $t->unique(['course_id', 'type']);
        });
        Schema::create('questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $t->text('prompt');
            $t->unsignedInteger('points')->default(1);
            $t->unsignedInteger('sequence');
            $t->timestamps();
            $t->unique(['assessment_id', 'sequence']);
        });
        Schema::create('answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->text('content');
            $t->boolean('is_correct')->default(false);
            $t->unsignedInteger('sequence');
            $t->timestamps();
            $t->unique(['question_id', 'sequence']);
        });
        Schema::create('assessment_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('assessment_id')->constrained()->restrictOnDelete();
            $t->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('attempt_number');
            $t->decimal('score', 5, 2);
            $t->boolean('passed')->nullable();
            $t->timestamp('submitted_at');
            $t->timestamps();
            $t->unique(['assessment_id', 'enrollment_id', 'attempt_number']);
            $t->index(['enrollment_id', 'submitted_at']);
        });
        Schema::create('assessment_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $t->foreignId('question_id')->constrained()->restrictOnDelete();
            $t->foreignId('answer_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_correct')->default(false);
            $t->timestamps();
            $t->unique(['assessment_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assessments');
    }
};
