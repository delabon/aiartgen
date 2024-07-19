<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertViewIs('login.create');
    }

    public function test_logins_user_successfully(): void
    {
        $password = '123456789';
        $user = User::factory()->create([
            'password' => $password
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('home');
        $this->assertTrue(Auth::check());
        $this->assertSame(Auth::user()->id, $user->id);
    }

    public function test_redirects_to_login_page_with_error_when_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'invalid passoword',
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    public function test_redirects_to_login_page_with_error_when_invalid_email(): void
    {
        $password = '123456789';
        User::factory()->create([
            'password' => $password
        ]);

        $response = $this->post('/login', [
            'email' => 'invalid-email@example.com',
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    #[DataProvider('invalidLoginCredentialsDataProvider')]
    public function test_redirects_to_login_page_with_errors_when_invalid_data(array $data, string $errorKey): void
    {
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([$errorKey]);
        $this->assertFalse(Auth::check());
    }

    public static function invalidLoginCredentialsDataProvider(): array
    {
        return [
            'No email' => [
                'data' => [
                    'password' => '123456789',
                ],
                'errorKey' => 'email'
            ],
            'Empty email' => [
                'data' => [
                    'email' => '',
                    'password' => '123456789',
                ],
                'errorKey' => 'email'
            ],
            'Invalid email' => [
                'data' => [
                    'email' => 'bla bla',
                    'password' => '123456789',
                ],
                'errorKey' => 'email'
            ],
            'No password' => [
                'data' => [
                    'email' => 'valid@example.com',
                ],
                'errorKey' => 'password'
            ],
            'Empty password' => [
                'data' => [
                    'email' => 'valid@example.com',
                    'password' => '',
                ],
                'errorKey' => 'password'
            ],
            'Small password' => [
                'data' => [
                    'email' => 'valid@example.com',
                    'password' => 'sas',
                ],
                'errorKey' => 'password'
            ],
            'Large password' => [
                'data' => [
                    'email' => 'valid@example.com',
                    'password' => str_repeat('a', 394),
                ],
                'errorKey' => 'password'
            ],
        ];
    }
}
