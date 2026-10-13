<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->string('whatsapp_status', 16)->default('queued');
            $table->string('whatsapp_error')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'announcement_id']);
            $table->index(['user_id', 'read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('announcements');
    }
};
