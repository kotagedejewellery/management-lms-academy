<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->foreignId('announcement_id')->nullable()->change();
            $table->string('dedupe_key', 160)->nullable()->after('announcement_id');
            $table->unique(['user_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'dedupe_key']);
            $table->dropColumn('dedupe_key');
            $table->foreignId('announcement_id')->nullable(false)->change();
        });
    }
};
