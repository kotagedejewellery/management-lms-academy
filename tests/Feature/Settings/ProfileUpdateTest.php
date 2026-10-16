<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_only_offers_email_verification_when_the_feature_is_enabled(): void
    {
        config(['fortify.features' => []]);

        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/Profile')
                ->where('mustVerifyEmail', false),
            );
    }

    public function test_shared_user_includes_an_unverified_email_status(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.user.email_verified_at')
                ->where('auth.user.email_verified_at', null),
            );
    }

    public function test_shared_user_includes_a_verified_email_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.user.email_verified_at'),
            );
    }

    public function test_settings_redirects_to_the_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings')
            ->assertRedirect(route('profile.edit'));
    }

    public function test_inactive_user_cannot_access_profile_settings(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_deletion_is_not_an_available_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/settings/profile')
            ->assertMethodNotAllowed();
    }
}
