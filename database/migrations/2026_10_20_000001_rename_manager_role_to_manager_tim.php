<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('slug', 'manager')
            ->where('name', 'Manager Divisi')
            ->update(['name' => 'Manager Tim']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('slug', 'manager')
            ->where('name', 'Manager Tim')
            ->update(['name' => 'Manager Divisi']);
    }
};
