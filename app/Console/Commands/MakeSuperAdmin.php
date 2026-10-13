<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'academy:make-super-admin {email : Email akun yang akan diberi akses Super Admin}';

    protected $description = 'Berikan role Super Admin kepada akun yang sudah ada.';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        $user->roles()->syncWithoutDetaching([
            Role::query()->where('slug', 'super-admin')->valueOrFail('id'),
        ]);

        $this->info("{$user->email} sekarang adalah Super Admin.");

        return self::SUCCESS;
    }
}
