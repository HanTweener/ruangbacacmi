<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_hashed_user_and_authenticated_session(): void
    {
        $password = fake()->password(16, 20).'12a';
        $this->post('/register', ['name' => 'Synthetic Author', 'email' => 'author@example.test', 'password' => $password, 'password_confirmation' => $password])->assertRedirect('/dashboard');
        $user = User::where('email', 'author@example.test')->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk()->assertSee('Halo, Synthetic Author');
    }

    public function test_registration_rejects_invalid_duplicate_and_weak_inputs(): void
    {
        User::factory()->create(['email' => 'exists@example.test']);
        $this->post('/register', ['name' => '', 'email' => 'exists@example.test', 'password' => 'short', 'password_confirmation' => 'different'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_rotates_session_and_logout_invalidates_access(): void
    {
        $password = fake()->password(18, 22);
        $user = User::factory()->create(['password' => $password]);
        $this->get('/login');
        $before = session()->getId();
        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_invalid_login_is_generic_and_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_login_rejects_non_scalar_email_without_server_error(): void
    {
        $this->post('/login', ['email' => ['invalid'], 'password' => 'invalid'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_recovery_hides_account_existence_and_uses_native_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $known = $this->post('/forgot-password', ['email' => $user->email]);
        $known->assertSessionHas('success');
        $message = session('success');
        $this->post('/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHas('success', $message);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_valid_reset_changes_password_rejects_replay_and_old_login(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $password = fake()->password(16, 20).'12a';
        $payload = ['token' => $token, 'email' => $user->email, 'password' => $password, 'password_confirmation' => $password];
        $this->post('/reset-password', $payload)->assertRedirect('/login');
        $this->assertTrue(Hash::check($password, $user->fresh()->password));
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_reset_rejects_invalid_and_expired_token(): void
    {
        $user = User::factory()->create();
        $password = fake()->password(16, 20).'12a';
        $payload = ['email' => $user->email, 'password' => $password, 'password_confirmation' => $password];
        $this->post('/reset-password', $payload + ['token' => 'invalid'])->assertSessionHasErrors('email');
        $token = Password::createToken($user);
        $this->travel(61)->minutes();
        $this->post('/reset-password', $payload + ['token' => $token])->assertSessionHasErrors('email');
    }

    public function test_auth_views_and_security_headers(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/reset-password/sample-token'] as $path) {
            $this->get($path)->assertOk()->assertHeader('X-Frame-Options', 'DENY')->assertHeader('Referrer-Policy', 'no-referrer');
        }
        $this->get('/login')->assertHeader('Cache-Control', 'no-store, private');
    }
}
