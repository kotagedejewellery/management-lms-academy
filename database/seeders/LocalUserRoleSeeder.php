<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LocalUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('LocalUserRoleSeeder hanya dapat dijalankan pada environment local atau testing.');
        }

        $this->call(RolePermissionSeeder::class);

        $branches = collect([
            'KGJ-KTG' => 'KGJ Kotagede',
            'KGJ-MRB' => 'KGJ Malioboro',
            'KGJ-SLM' => 'KGJ Sleman',
        ])->map(fn (string $name, string $code): Branch => Branch::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]));
        $divisions = collect([
            'SLS' => 'Sales',
            'OPS' => 'Operasional',
            'HR' => 'Human Resources',
        ])->map(fn (string $name, string $code): Division => Division::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]));
        $positions = [
            'sales_associate' => Position::query()->updateOrCreate(['division_id' => $divisions['SLS']->id, 'name' => 'Sales Associate'], ['is_active' => true]),
            'senior_sales' => Position::query()->updateOrCreate(['division_id' => $divisions['SLS']->id, 'name' => 'Senior Sales Associate'], ['is_active' => true]),
            'store_leader' => Position::query()->updateOrCreate(['division_id' => $divisions['OPS']->id, 'name' => 'Store Leader'], ['is_active' => true]),
            'hr_officer' => Position::query()->updateOrCreate(['division_id' => $divisions['HR']->id, 'name' => 'HR Officer'], ['is_active' => true]),
        ];
        $roles = Role::query()->whereIn('slug', ['super-admin', 'lms-admin', 'trainer', 'employee'])->get()->keyBy('slug');
        $password = Hash::make('password');
        $verifiedAt = Carbon::now();

        $this->upsertUser($roles['super-admin'], [
            'employee_id' => 'LOCAL-ADMIN', 'name' => 'Local Super Admin', 'email' => 'admin@kgj.test', 'phone' => null,
            'division_id' => null, 'position_id' => null, 'branch_id' => null, 'manager_id' => null, 'status' => 'active',
        ], $password, $verifiedAt);
        $this->upsertUser($roles['lms-admin'], [
            'employee_id' => 'KGJ-001', 'name' => 'Maya Pradipta', 'email' => 'lms.admin@kgj.test', 'phone' => '081200000001',
            'division_id' => $divisions['HR']->id, 'position_id' => $positions['hr_officer']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'manager_id' => null, 'status' => 'active',
        ], $password, $verifiedAt);
        $this->upsertUser($roles['trainer'], [
            'employee_id' => 'KGJ-002', 'name' => 'Raka Wicaksana', 'email' => 'trainer@kgj.test', 'phone' => '081200000002',
            'division_id' => $divisions['HR']->id, 'position_id' => $positions['hr_officer']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'manager_id' => null, 'status' => 'active',
        ], $password, $verifiedAt);
        $manager = $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-010', 'name' => 'Dewi Kurniasari', 'email' => 'manager@kgj.test', 'phone' => '081200000010',
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['senior_sales']->id, 'branch_id' => $branches['KGJ-MRB']->id, 'manager_id' => null, 'status' => 'active',
        ], $password, $verifiedAt);
        foreach ([
            ['employee_id' => 'KGJ-101', 'name' => 'Anisa Putri', 'email' => 'anisa@kgj.test', 'phone' => '081200000101', 'branch_id' => $branches['KGJ-MRB']->id],
            ['employee_id' => 'KGJ-102', 'name' => 'Bima Aditya', 'email' => 'bima@kgj.test', 'phone' => '081200000102', 'branch_id' => $branches['KGJ-KTG']->id],
            ['employee_id' => 'KGJ-103', 'name' => 'Citra Lestari', 'email' => 'citra@kgj.test', 'phone' => '081200000103', 'branch_id' => $branches['KGJ-SLM']->id],
        ] as $employee) {
            $this->upsertUser($roles['employee'], [...$employee, 'division_id' => $divisions['SLS']->id, 'position_id' => $positions['sales_associate']->id, 'manager_id' => $manager->id, 'status' => 'active'], $password, $verifiedAt);
        }
        $this->upsertUser($roles['lms-admin'], [
            'employee_id' => 'KGJ-900', 'name' => 'Nadia Utami', 'email' => 'owner@kgj.test', 'phone' => '081200000900',
            'division_id' => $divisions['OPS']->id, 'position_id' => $positions['store_leader']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'manager_id' => null, 'status' => 'active',
        ], $password, $verifiedAt);

    }

    /** @param array<string, mixed> $attributes */
    private function upsertUser(Role $role, array $attributes, string $password, Carbon $verifiedAt): User
    {
        $user = User::query()->firstOrNew(['email' => $attributes['email']]);
        $user->fill($attributes);

        if (! $user->exists) {
            $user->password = $password;
        }

        $user->email_verified_at ??= $verifiedAt;
        $user->save();
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
