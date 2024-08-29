<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FeatureTestCase;

class ResetPasswordTest extends FeatureTestCase
{
    use RefreshDatabase;

    ///
    /// Send email tests
    ///

    public function test_returns_send_token_email_view_correctly(): void
    {
        $this->get('/password-reset')
            ->assertOk()
            ->assertViewIs('password-reset.create');
    }

    public function test_sends_reset_password_email_successfully(): void
    {
        Mail::fake();

        $email = 'john@doe.co';
        User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->post('/password-reset/send', [
            'email' => $email
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHas('success', 'An email with the reset link has been sent to your email address.');

        $tokens = DB::table('password_reset_tokens')->get();

        $this->assertCount(1, $tokens);
        $this->assertSame($email, $tokens[0]->email);

        Mail::assertQueued(PasswordResetMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    #[DataProvider('invalidEmailDataProvider')]
    public function test_send_reset_password_fails_on(array $data, string $errorMessage): void
    {
        $response = $this->post('/password-reset/send', $data);

        $response->assertSessionHasErrors([
            'email' => $errorMessage,
        ]);

        $tokens = DB::table('password_reset_tokens')->get();

        $this->assertCount(0, $tokens);
    }

    public static function invalidEmailDataProvider(): array
    {
        return [
            'No email' => [
                'data' => [],
                'errorMessage' => 'The email field is required.'
            ],
            'Empty email' => [
                'data' => [
                    'email' => ''
                ],
                'errorMessage' => 'The email field is required.'
            ],
            'Invalid email' => [
                'data' => [
                    'email' => 'sadsad #@$#@ '
                ],
                'errorMessage' => 'The email field must be a valid email address.'
            ],
            'Email does not exist' => [
                'data' => [
                    'email' => 'sabri@doe.fr'
                ],
                'errorMessage' => 'The selected email is invalid.'
            ],
        ];
    }

    public function test_rate_limiting_sending_reset_password_email_to_5_attempts_per_1_minute(): void
    {
        $email = 'john@doe.co';
        User::factory()->create([
            'email' => $email,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/password-reset/send', [
                'email' => $email
            ]);
        }

        $response = $this->post('/password-reset/send', [
            'email' => $email
        ]);

        $response->assertTooManyRequests();
    }

    ///
    /// Reset password page tests
    ///

    public function test_loads_edit_password_page_successfully(): void
    {
        $email = 'john@doe.co';
        $user = User::factory()->create([
            'email' => $email,
        ]);
        $token = Password::createToken($user);

        $this->get('/password-reset/' . $token . '-' . $user->id)
            ->assertOk()
            ->assertViewIs('password-reset.edit');
    }

    /**
     * Do not add a non-existent or empty token test because it redirects you to the create controller
     */
    public function test_returns_not_found_when_trying_to_load_edit_password_page_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@doe.co',
        ]);

        $this->get('/password-reset/kljsdckjkjkkdslkfd8934re98435985439kjldsk-' . $user->id)
            ->assertNotFound();
    }

    public function test_returns_not_found_when_trying_to_load_edit_password_page_with_non_existent_user_id(): void
    {
        $user = User::factory()->create([
            'email' => 'john@doe.co',
        ]);
        $token = Password::createToken($user);

        $this->get('/password-reset/' . $token . '-' . 895698)
            ->assertNotFound();
    }

    ///
    /// Reset password tests
    ///

    public function test_resets_password_successfully(): void
    {
        $password = '000001';
        $user = User::factory()->create([
            'email' => 'john@doe.co',
        ]);
        $token = Password::createToken($user);

        $response = $this->patch('/password-reset/' . $user->id, [
            'reset_password_token' => $token,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHas('success', 'Your password has been updated.');

        $tokens = DB::table('password_reset_tokens')->get();
        $this->assertCount(0, $tokens);

        $refreshedUser = $user->refresh();
        $this->assertTrue(Hash::check($password, $refreshedUser->password));
    }

    public function test_returns_not_found_response_when_user_does_not_exist(): void
    {
        $response = $this->patch('/password-reset/' . 37474, [
            'reset_password_token' => 'kleoroeroewr',
            'password' => 'kdfkdsfkfk',
            'password_confirmation' => 'kdfkdsfkfk'
        ]);

        $response->assertNotFound();
    }

    #[DataProvider('invalidDataProvider')]
    public function test_resetting_password_fails_on(array $data, string $errorKey, string $message): void
    {
        $user = User::factory()->create();

        $response = $this->patch('/password-reset/' . $user->id, $data);

        $response->assertSessionHasErrors([$errorKey => $message]);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'No token' => [
                'data' => [
                    'password' => 'kdfkdsfkfk',
                    'password_confirmation' => 'kdfkdsfkfk'
                ],
                'errorKey' => 'reset_password_token',
                'message' => 'The reset password token field is required.'
            ],
            'Empty token' => [
                'data' => [
                    'reset_password_token' => '',
                    'password' => 'kdfkdsfkfk',
                    'password_confirmation' => 'kdfkdsfkfk'
                ],
                'errorKey' => 'reset_password_token',
                'message' => 'The reset password token field is required.'
            ],
            'Non-string token' => [
                'data' => [
                    'reset_password_token' => true,
                    'password' => 'kdfkdsfkfk',
                    'password_confirmation' => 'kdfkdsfkfk'
                ],
                'errorKey' => 'reset_password_token',
                'message' => 'The reset password token field must be a string.'
            ],
            'No password' => [
                'data' => [
                    'reset_password_token' => 'qwekeriu3983knj243i24',
                    'password_confirmation' => 'kdfkdsfkfk'
                ],
                'errorKey' => 'password',
                'message' => 'The password field is required.'
            ],
            'Empty password' => [
                'data' => [
                    'reset_password_token' => 'qwekeriu3983knj243i24',
                    'password' => '',
                    'password_confirmation' => 'kdfkdsfkfk'
                ],
                'errorKey' => 'password',
                'message' => 'The password field is required.'
            ],
            'Small password' => [
                'data' => [
                    'reset_password_token' => 'qwekeriu3983knj243i24',
                    'password' => '123',
                    'password_confirmation' => '123'
                ],
                'errorKey' => 'password',
                'message' => 'The password field must be at least 5 characters.'
            ],
            'Large password' => [
                'data' => [
                    'reset_password_token' => 'qwekeriu3983knj243i24',
                    'password' => str_repeat('a', 55),
                    'password_confirmation' => str_repeat('a', 55)
                ],
                'errorKey' => 'password',
                'message' => 'The password field must not be greater than 20 characters.'
            ],
            'The password does not match' => [
                'data' => [
                    'reset_password_token' => 'qwekeriu3983knj243i24',
                    'password' => '123456',
                    'password_confirmation' => '123456ttewrewr'
                ],
                'errorKey' => 'password',
                'message' => 'The password field confirmation does not match.'
            ],
        ];
    }

    public function test_resetting_password_fails_on_when_token_is_not_associated_with_the_user(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $user2 = User::factory()->create();

        $response = $this->patch('/password-reset/' . $user2->id, [
            'reset_password_token' => $token,
            'password' => 'kdfkdsfk',
            'password_confirmation' => 'kdfkdsfk',
        ]);

        $response->assertSessionHasErrors(['token' => 'The token does not exist or is not associated with the user.']);
    }

    public function test_rate_limiting_resetting_password_email_to_5_attempts_per_1_minute(): void
    {
        $password = '000001';
        $user = User::factory()->create([
            'email' => 'john@doe.co',
        ]);
        $token = Password::createToken($user);

        for ($i = 0; $i < 5; $i++) {
            $this->patch('/password-reset/' . $user->id, [
                'reset_password_token' => $token,
                'password' => $password,
                'password_confirmation' => $password
            ]);
        }

        $response = $this->patch('/password-reset/' . $user->id, [
            'reset_password_token' => $token,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        $response->assertTooManyRequests();
    }
}
