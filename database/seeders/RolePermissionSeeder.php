<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'manage-users' => 'Kelola pengguna',
            'manage-organization' => 'Kelola struktur organisasi',
            'manage-courses' => 'Kelola course',
            'manage-learning-paths' => 'Kelola learning path',
            'manage-enrollments' => 'Kelola assignment belajar',
        ])->mapWithKeys(fn (string $name, string $slug) => [
            $slug => Permission::query()->updateOrCreate(['slug' => $slug], ['name' => $name]),
        ]);

        $roles = collect([
            'super-admin' => 'Super Admin',
            'lms-admin' => 'LMS Admin / HR',
            'trainer' => 'Trainer / Mentor',
            'employee' => 'Employee / Learner',
        ])->mapWithKeys(fn (string $name, string $slug) => [
            $slug => Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name]),
        ]);

        $roles['super-admin']->permissions()->sync($permissions->pluck('id'));
        $roles['lms-admin']->permissions()->sync($permissions->only(['manage-users', 'manage-organization', 'manage-courses', 'manage-learning-paths', 'manage-enrollments'])->pluck('id'));
        $roles['trainer']->permissions()->sync($permissions->only(['manage-courses'])->pluck('id'));
        $roles['employee']->permissions()->sync([]);
    }
}
