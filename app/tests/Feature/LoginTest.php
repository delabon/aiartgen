<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FeatureTestCase;

class LoginTest extends FeatureTestCase
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
        $response->assertSessionHas('success', 'You have signed-in successfully.');
        $this->assertTrue(Auth::check());
        $this->assertSame(Auth::user()->id, $user->id);
    }

    public function test_regenerates_session_id_after_login_successfully(): void
    {
        $password = '9983484';
        $user = User::factory()->create([
            'password' => $password
        ]);

        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $newSessionId = session()->getId();

        $this->assertNotSame($oldSessionId, $newSessionId);
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

    public function test_prevents_login_when_user_email_is_unverified(): void
    {
        $password = '123456789';
        $user = User::factory()->unverified()->create([
            'password' => $password
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHas('error', 'You need to verify your email address.');
        $this->assertFalse(Auth::check());
    }

    public function test_rate_limiting_login_to_5_attempts_per_1_minute(): void
    {
        $email = 'test@example.com';
        $password = '123456789';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => $password,
            ]);
        }

        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertTooManyRequests();
    }
}
