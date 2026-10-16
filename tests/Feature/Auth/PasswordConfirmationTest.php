<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/ConfirmPassword'),
        );
    }

    public function test_password_confirmation_requires_authentication()
    {
        $response = $this->get(route('password.confirm'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_confirm_their_password_without_an_identifier_column(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('password.confirm.store'), [
                'password' => 'password',
            ]);

        $response
            ->assertRedirect(config('fortify.home'))
            ->assertSessionHasNoErrors();

        $this->assertNotNull(
            $response->getSession()->get('auth.password_confirmed_at'),
        );
    }
}
