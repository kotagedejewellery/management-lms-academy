<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_page_only_exposes_password_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/Security')
                ->has('passwordRules')
                ->missing('canManagePasskeys')
                ->missing('passkeys')
                ->missing('canManageTwoFactor')
                ->missing('twoFactorEnabled'),
            );
    }

    public function test_security_page_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('security.edit'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_two_factor_and_passkey_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('two-factor.login'));
        $this->assertFalse(Route::has('well-known.passkeys'));
    }

    public function test_unverified_user_is_redirected_to_email_verification_from_security_and_appearance(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('security.edit'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('appearance.edit'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('security.edit'))
            ->put(route('user-password.update'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('security.edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('security.edit'))
            ->put(route('user-password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('security.edit'));
    }
}
