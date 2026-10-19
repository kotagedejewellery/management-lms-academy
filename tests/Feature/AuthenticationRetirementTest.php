<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationRetirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_retired_two_factor_and_passkey_storage_is_removed(): void
    {
        $this->assertFalse(Schema::hasTable('passkeys'));
        $this->assertFalse(Schema::hasColumns('users', [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]));
    }

    public function test_chisel_does_not_offer_retired_authentication_features(): void
    {
        $chisel = file_get_contents(base_path('chisel.php'));
        $paths = file_get_contents(base_path('chisel-paths.php'));

        $this->assertIsString($chisel);
        $this->assertIsString($paths);
        $this->assertStringNotContainsString("'2fa' =>", $chisel);
        $this->assertStringNotContainsString("'passkeys' =>", $chisel);
        $this->assertStringNotContainsString('TwoFactor', $paths);
        $this->assertStringNotContainsString('Passkey', $paths);
    }
}
