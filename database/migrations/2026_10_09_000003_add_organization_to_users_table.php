<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('name');
            $table->foreignId('division_id')->nullable()->after('email_verified_at')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('division_id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('position_id')->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('active')->after('manager_id');
            $table->index(['division_id', 'position_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['division_id', 'position_id', 'branch_id', 'status']);
            $table->dropConstrainedForeignId('manager_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('division_id');
            $table->dropUnique(['employee_id']);
            $table->dropColumn(['employee_id', 'status']);
        });
    }
};
