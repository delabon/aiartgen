<?php

namespace Tests\Unit\Models;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_implements_correct_interfaces(): void
    {
        $user = new User();
        $this->assertInstanceOf(CanResetPassword::class, $user);
        $this->assertInstanceOf(CanResetPassword::class, $user);
    }

    public function test_getEmailForPasswordReset_returns_email_correctly(): void
    {
        $email = 'jona@doe.co';
        $user = new User(['email' => $email]);

        $this->assertSame($email, $user->getEmailForPasswordReset());
    }

    public function test_getEmailForPasswordReset_returns_null_when_no_email(): void
    {
        $user = new User();

        $this->assertNull($user->getEmailForPasswordReset());
    }

    public function test_sendPasswordResetNotification_sends_password_reset_email(): void
    {
        Mail::fake();
        $email = 'gao@doe.fr';
        $user = new User(['email' => $email]);

        $user->sendPasswordResetNotification('My Fake Token');

        Mail::assertQueued(PasswordResetMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function test_hasVerifiedEmail_return_true_when_email_is_verified(): void
    {
        $user = new User([
            'email_verified_at' => Carbon::createFromFormat('Y-m-d', '2021-01-01')
        ]);

        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_hasVerifiedEmail_return_false_when_email_is_unverified(): void
    {
        $user = new User();

        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_markEmailAsVerified_makes_email_verified(): void
    {
        $user = new User();

        $user->markEmailAsVerified();

        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_getEmailForVerification_returns_email_correctly(): void
    {
        $email = 'sami@doe.co';
        $user = new User([
            'email' => $email
        ]);

        $this->assertSame($email, $user->getEmailForVerification());
    }

    public function test_sendEmailVerificationNotification_sends_verification_email(): void
    {
        Notification::fake();

        $email = 'sami@doe.co';
        $user = new User([
            'id' => 1,
            'email' => $email
        ]);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->assertFalse($user->hasVerifiedEmail());
    }
}
