<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterTest extends TestCase
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
        $email = 'john@example.com';
        $name = 'John Doe';
        $password = '123456789';
        $username = 'johndoe';

        $response = $this->post('/register', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
            'username' => $username,
        ]);

        $response->assertRedirectToRoute('login');
        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertSame($email, $users[0]->email);
        $this->assertSame($name, $users[0]->name);
        $this->assertSame($username, $users[0]->username);
        $this->assertNotEquals($password, $users[0]->password);
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
        $email = 'john@example.com';
        $name = 'John Doe';
        $password = '123456789';
        $username = 'johndoe';

        User::factory()->create([
            'username' => $username,
        ]);

        $response = $this->post('/register', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
            'username' => $username,
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
}
