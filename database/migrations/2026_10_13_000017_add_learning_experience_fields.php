<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->longText('transcript')->nullable()->after('content');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('prompt');
        });

        Schema::create('assessment_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->json('answers')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_drafts');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('transcript');
        });
    }
};
