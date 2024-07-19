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

        $response = $this->post('/register', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
        ]);

        $response->assertRedirectToRoute('login');
        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertSame($email, $users[0]->email);
        $this->assertSame($name, $users[0]->name);
        $this->assertNotEquals($password, $users[0]->password);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $email = 'john@example.com';
        $name = 'John Doe';
        $password = '123456789';
        User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->post('/register', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'name' => $name,
        ]);

        $response->assertRedirectToRoute('register.create');
        $response->assertSessionHasErrors(['email']);

        $users = User::all();

        $this->assertCount(1, $users);
    }

    #[DataProvider('invalidRegisterDataProvider')]
    public function test_registration_fails_on_invalid_data(array $data, string $errorKey): void
    {
        $response = $this->post('/register', $data);

        $response->assertRedirectToRoute('register.create');
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
                ],
                'errorKey' => 'email'
            ],
            'Empty email' => [
                'data' => [
                    'email' => '',
                    'name' => 'John Doe',
                    'password' => '13124445',
                ],
                'errorKey' => 'email'
            ],
            'Invalid email' => [
                'data' => [
                    'email' => 'jkaskdk $@#%',
                    'name' => 'John Doe',
                    'password' => '13124445',
                ],
                'errorKey' => 'email'
            ],
            'No email confirmation' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'password' => '3424324'
                ],
                'errorKey' => 'email'
            ],
            'No password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                ],
                'errorKey' => 'password'
            ],
            'Empty password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'password' => ''
                ],
                'errorKey' => 'password'
            ],
            'Small password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'password' => 'dw'
                ],
                'errorKey' => 'password'
            ],
            'Large password' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'password' => str_repeat('a', 345)
                ],
                'errorKey' => 'password'
            ],
            'No password confirmation' => [
                'data' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'password' => str_repeat('a', 345)
                ],
                'errorKey' => 'password'
            ],
            'No name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4'
                ],
                'errorKey' => 'name'
            ],
            'Empty name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'name' => ''
                ],
                'errorKey' => 'name'
            ],
            'Small name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'name' => 'a'
                ],
                'errorKey' => 'name'
            ],
            'Large name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'name' => str_repeat('a', 257),
                ],
                'errorKey' => 'name'
            ],
            'Invalid name' => [
                'data' => [
                    'email' => 'john@example.com',
                    'password' => 'asd3e2q4',
                    'name' => 'asdlasd #$%$#%^ sd;.,/',
                ],
                'errorKey' => 'name'
            ],
        ];
    }
}
