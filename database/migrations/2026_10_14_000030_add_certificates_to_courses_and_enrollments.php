<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->boolean('certificate_enabled')->default(false)->after('requires_post_test');
        });

        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'issued_at']);
            $table->index(['course_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('certificate_enabled');
        });
    }
};
