<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->text('description')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('learning_path_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('sequence');
            $table->timestamps();

            $table->unique(['learning_path_id', 'course_id']);
            $table->unique(['learning_path_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_courses');
        Schema::dropIfExists('learning_paths');
    }
};
