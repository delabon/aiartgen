<?php

namespace Tests\Unit\Models;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_implements_correct_interfaces(): void
    {
        $this->assertInstanceOf(CanResetPassword::class, new User());
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

        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
