<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\FeatureTestCase;

class EmailVerificationTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_verifies_email_successfully(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email)
            ]
        );

        $this->assertFalse($user->hasVerifiedEmail());

        $response = $this->get($verificationUrl);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHas([
            'success' => 'Your email has been verified.'
        ]);
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_returns_not_found_response_when_id_does_not_exist(): void
    {
        $response = $this->get(route('verification.verify', [
            'id' => 8585,
            'hash' => uniqid()
        ]));

        $response->assertNotFound();
    }

    public function test_returns_home_when_hash_is_invalid(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => uniqid()
        ]));

        $response->assertRedirectToRoute('home');
        $response->assertSessionHas([
            'error' => 'Invalid verification link.'
        ]);
    }
}
