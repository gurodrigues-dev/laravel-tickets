<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ApiEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified_via_api_without_authentication(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email verified successfully',
            ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Invalid verification link',
            ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_fails_for_nonexistent_user(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => 9999, 'hash' => sha1('test@example.com')]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found',
            ]);
    }

    public function test_verification_returns_message_if_already_verified(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email already verified',
            ]);
    }

    public function test_verification_fails_with_expired_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->subMinutes(61),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(403);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_fails_with_tampered_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $tamperedUrl = str_replace('signature=', 'signature=tampered', $verificationUrl);

        $response = $this->get($tamperedUrl);

        $response->assertStatus(403);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_throttle_limits_verification_attempts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        for ($i = 0; $i < 6; $i++) {
            $response = $this->get($verificationUrl);
            if ($i < 6) {
                $response->assertStatus(200);
            }
        }

        $response = $this->get($verificationUrl);
        $response->assertStatus(429);
    }
}
