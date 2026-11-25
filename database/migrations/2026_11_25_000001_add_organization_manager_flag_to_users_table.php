<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_organization_manager')->default(false);
        });

        DB::table('users')
            ->whereIn('id', DB::table('users')->whereNotNull('manager_id')->select('manager_id'))
            ->update(['is_organization_manager' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_organization_manager');
        });
    }
};
