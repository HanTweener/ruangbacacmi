<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_blocks_post_without_token_when_test_bypass_is_disabled(): void
    {
        $this->app['env'] = 'local';
        $this->post('/login', ['email' => 'synthetic@example.test', 'password' => 'invalid'])->assertStatus(419);
        $this->post('/register', [])->assertStatus(419);
    }

    public function test_password_reset_invalidates_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create();
        $other = User::factory()->create();
        foreach ([$user, $other] as $account) {
            DB::table('sessions')->insert(['id' => 'synthetic-session-'.$account->id, 'user_id' => $account->id, 'payload' => base64_encode(''), 'last_activity' => time()]);
        }
        $token = Password::createToken($user);
        $password = fake()->password(18, 20).'12a';
        $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => $password, 'password_confirmation' => $password])->assertRedirect('/login');
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('sessions', ['user_id' => $other->id]);
    }
}
