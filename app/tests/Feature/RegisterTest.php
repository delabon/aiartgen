<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FeatureTestCase;

class RegisterTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertViewIs('register.create');
    }

    public function test_registers_user_successfully(): void
    {
        Notification::fake();

        $userData = $this->getUserData();

        $response = $this->post('/register', [
            'email' => $userData['email'],
            'email_confirmation' => $userData['email'],
            'password' => $userData['password'],
            'password_confirmation' => $userData['password'],
            'name' => $userData['name'],
            'username' => $userData['username'],
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHas('success', 'Your account has been created.');

        /** @var User[] $users */
        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertSame($userData['email'], $users[0]->email);
        $this->assertSame($userData['name'], $users[0]->name);
        $this->assertSame($userData['username'], $users[0]->username);
        $this->assertNotEquals($userData['password'], $users[0]->password);

        Notification::assertSentTo($users[0], VerifyEmail::class);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $email = 'john@example.com';
        $name = 'John Doe';
        $password = '123456789';
        $username = 'johndoe';

        User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->post('/register', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
            'username' => $username,
        ]);

        $response->assertSessionHasErrors(['email']);

        $users = User::all();

        $this->assertCount(1, $users);
    }

    public function test_registration_fails_with_duplicate_username(): void
    {
        $userData = $this->getUserData();

        User::factory()->create([
            'username' => $userData['username'],
        ]);

        $response = $this->post('/register', [
            'email' => $userData['email'],
            'email_confirmation' => $userData['email'],
            'password' => $userData['password'],
            'password_confirmation' => $userData['password'],
            'name' => $userData['name'],
            'username' => $userData['username'],
        ]);

        $response->assertSessionHasErrors(['username']);

        $users = User::all();

        $this->assertCount(1, $users);
    }

    #[DataProvider('invalidRegisterDataProvider')]
    public function test_registration_fails_on_invalid_data(array $data, string $errorKey): void
    {
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([$errorKey]);
        $this->assertCount(0, User::all());
    }

    public static function invalidRegisterDataProvider(): array
    {
        return [
            'No email' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'username' => 'johndoe',
                ],
                'errorKey' => 'email'
            ],
            'Empty email' => [
                'data' => [
                    'email' => '',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => '13124445',
                ],
                'errorKey' => 'email'
            ],
            'Invalid email' => [
                'data' => [
                    'email' => 'jkaskdk $@#%',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => '13124445',
                ],
                'errorKey' => 'email'
            ],
            'No email confirmation' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => '3424324'
                ],
                'errorKey' => 'email'
            ],
            'No password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'username' => 'johndoe',
                    'name' => 'John Doe',
                ],
                'errorKey' => 'password'
            ],
            'Empty password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => ''
                ],
                'errorKey' => 'password'
            ],
            'Small password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => 'dw'
                ],
                'errorKey' => 'password'
            ],
            'Large password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => str_repeat('a', 345)
                ],
                'errorKey' => 'password'
            ],
            'No password confirmation' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'password' => str_repeat('a', 345)
                ],
                'errorKey' => 'password'
            ],
            'No name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'username' => 'johndoe',
                    'password' => 'asd3e2q4'
                ],
                'errorKey' => 'name'
            ],
            'Empty name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'username' => 'johndoe',
                    'password' => 'asd3e2q4',
                    'name' => ''
                ],
                'errorKey' => 'name'
            ],
            'Small name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'username' => 'johndoe',
                    'name' => 'a'
                ],
                'errorKey' => 'name'
            ],
            'Large name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'username' => 'johndoe',
                    'name' => str_repeat('a', 257),
                ],
                'errorKey' => 'name'
            ],
            'Invalid name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'username' => 'johndoe',
                    'name' => 'asdlasd #$%$#%^ sd;.,/',
                ],
                'errorKey' => 'name'
            ],
            'No username' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'email' => 'john@example.com',
                ],
                'errorKey' => 'username'
            ],
            'Empty username' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'email' => 'john@example.com',
                    'username' => '',
                ],
                'errorKey' => 'username'
            ],
            'Invalid username' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'email' => 'john@example.com',
                    'username' => 'asl12340 #!@$8679- sdd',
                ],
                'errorKey' => 'username'
            ],
            'Small username' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'email' => 'john@example.com',
                    'username' => 'a',
                ],
                'errorKey' => 'username'
            ],
            'Large username' => [
                'data' => [
                    'name' => 'John Doe',
                    'password' => '13124445',
                    'email' => 'john@example.com',
                    'username' => str_repeat('a', 51),
                ],
                'errorKey' => 'username'
            ],
        ];
    }

    public function test_keeps_new_user_logged_out_after_registration(): void
    {
        $userData = $this->getUserData();

        $this->post('/register', [
            'email' => $userData['email'],
            'email_confirmation' => $userData['email'],
            'password' => $userData['password'],
            'password_confirmation' => $userData['password'],
            'name' => $userData['name'],
            'username' => $userData['username'],
        ]);

        $this->assertFalse(Auth::check());
    }

    public function test_rate_limiting_register_to_5_attempts_per_1_minute(): void
    {
        $email = 'jane@example.com';
        $name = 'Jane Doe';
        $password = '123456789';
        $username = 'jane';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'email' => $i . $email,
                'email_confirmation' => $i . $email,
                'password' => $password,
                'password_confirmation' => $password,
                'name' => $name,
                'username' => $username . $i,
            ]);
        }

        $response = $this->post('/register', [
            'email' => '6' . $email,
            'email_confirmation' => '6' . $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
            'username' => $username . '6',
        ]);

        $response->assertTooManyRequests();
    }

    public function test_user_should_have_access_token_after_registration(): void
    {
        $userData = $this->getUserData();

        $this->post('/register', [
            'email' => $userData['email'],
            'email_confirmation' => $userData['email'],
            'password' => $userData['password'],
            'password_confirmation' => $userData['password'],
            'name' => $userData['name'],
            'username' => $userData['username'],
        ]);

        $user = User::first();

        $this->assertGreaterThan(0, $user->tokens->count());
        $this->assertSame('user-token', $user->tokens[0]->name);
    }

    private function getUserData(): array
    {
        return [
            'email' => 'john@example.com',
            'password' => '123456789',
            'name' => 'John Doe',
            'username' => 'johndoe',
        ];
    }
}
