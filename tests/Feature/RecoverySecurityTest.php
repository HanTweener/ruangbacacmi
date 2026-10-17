<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RecoverySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_password_session_is_rejected_even_on_private_image_route(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $oldHash = Auth::guard()->hashPasswordForCookie($user->password);
        $article = Article::factory()->for($user)->create(['gambar' => 'articles/synthetic.png']);
        Storage::disk('local')->put($article->gambar, 'synthetic-image');
        $user->update(['password' => fake()->password(20, 24)]);
        $this->actingAs($user->fresh())->withSession(['password_hash_web' => $oldHash])->get(route('article.image', $article))->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_mail_failure_uses_generic_response_and_logs_no_exception_details(): void
    {
        Log::spy();
        Password::shouldReceive('sendResetLink')->once()->andThrow(new TransportException('SENSITIVE_SYNTHETIC_MARKER'));
        $this->post('/forgot-password', ['email' => 'synthetic@example.test'])->assertSessionHas('success');
        Log::shouldHaveReceived('warning')->once()->with('Password recovery mail transport is unavailable.');
        $this->assertSame('array', config('mail.mailers.log.transport'));
    }

    public function test_reset_notification_uses_configured_canonical_origin(): void
    {
        config(['app.url' => 'https://canonical.example.test']);
        $user = User::factory()->create();
        $mail = (new ResetPassword('synthetic-token'))->toMail($user);
        $this->assertStringStartsWith('https://canonical.example.test/reset-password/', $mail->actionUrl);
    }
}
