<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $mappings = ['manager' => 'employee', 'management' => 'lms-admin'];
            $roleIds = DB::table('roles')
                ->whereIn('slug', array_merge(array_keys($mappings), array_values($mappings)))
                ->pluck('id', 'slug');

            foreach ($mappings as $retiredRole => $replacementRole) {
                if ($roleIds->get($retiredRole) !== null && $roleIds->get($replacementRole) === null) {
                    throw new RuntimeException("Migrasi role dibatalkan: role pengganti {$replacementRole} tidak tersedia.");
                }
            }

            foreach ($mappings as $retiredRole => $replacementRole) {
                $retiredRoleId = $roleIds->get($retiredRole);

                if ($retiredRoleId === null) {
                    continue;
                }

                $replacementRoleId = $roleIds->get($replacementRole);
                $userIds = DB::table('user_roles')
                    ->where('role_id', $retiredRoleId)
                    ->pluck('user_id');

                foreach (array_chunk($userIds->all(), 500) as $userIdChunk) {
                    DB::table('user_roles')->insertOrIgnore(array_map(
                        fn (int $userId): array => ['user_id' => $userId, 'role_id' => $replacementRoleId],
                        $userIdChunk,
                    ));
                }

                DB::table('user_roles')->where('role_id', $retiredRoleId)->delete();
            }

            DB::table('roles')->whereIn('slug', array_keys($mappings))->delete();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Migrasi role ini bersifat forward-only. Pulihkan backup atau buat migration korektif; rollback tidak dapat mengembalikan relasi role pengguna dengan aman.');
    }
};
