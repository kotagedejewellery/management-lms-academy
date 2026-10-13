<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        if (app()->environment('local')) {
            $admin = User::query()->firstOrCreate(
                ['email' => 'admin@kgj.test'],
                [
                    'name' => 'Local Super Admin',
                    'employee_id' => 'LOCAL-ADMIN',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );

            $admin->roles()->syncWithoutDetaching([
                Role::query()->where('slug', 'super-admin')->valueOrFail('id'),
            ]);
        }
    }
}
