<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_path_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['learning_path_id', 'division_id', 'position_id', 'branch_id']);
            $table->index(['division_id', 'position_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_rules');
    }
};
