<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_screen_can_be_rendered(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_a_link_is_sent_for_a_known_login(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['login' => $user->login])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, PasswordSetupLink::class);
        $this->assertDatabaseHas('password_reset_tokens', ['user_id' => $user->id]);
    }

    public function test_no_link_and_no_leak_for_an_unknown_login(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['login' => 'inconnu'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_setup_screen_requires_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = PasswordResetToken::issueFor($user);

        $this->get(route('password.setup', ['token' => $token->token]))->assertOk();
        $this->get(route('password.setup', ['token' => 'invalide']))
            ->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_set_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = PasswordResetToken::issueFor($user);

        $response = $this->post(route('password.store', ['token' => $token->token]), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $user->refresh()->password));
        $this->assertTrue($token->refresh()->used);
    }

    public function test_a_used_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = PasswordResetToken::issueFor($user);
        $token->markUsed();

        $this->post(route('password.store', ['token' => $token->token]), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasErrors('password');
    }
}
