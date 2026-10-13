<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('course_categories')->restrictOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prerequisite_course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedTinyInteger('passing_grade')->default(70);
            $table->string('status', 16)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'division_id', 'position_id']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('sequence');
            $table->timestamps();

            $table->unique(['course_id', 'sequence']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 16)->default('article');
            $table->longText('content')->nullable();
            $table->string('resource_url', 2048)->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sequence');
            $table->timestamps();

            $table->unique(['module_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_categories');
    }
};
