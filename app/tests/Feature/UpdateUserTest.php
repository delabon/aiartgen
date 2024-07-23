<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    private ?User $user;
    private ?string $oldHashedPassword;
    private ?Carbon $oldUpdatedAt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'john@doe.com',
            'name' => 'John Doe',
            'username' => 'john',
            'password' => '123456',
            'created_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
            'updated_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
        ]);

        $this->oldHashedPassword = $this->user->password;
        $this->oldUpdatedAt = $this->user->updated_at;
    }

    public function test_returns_correct_view(): void
    {
        $this->actingAs($this->user);

        $this->get('/settings')
            ->assertOk()
            ->assertViewIs('settings.edit');
    }

    public function test_redirects_to_login_page_when_trying_to_access_settings_page_when_guest(): void
    {
        $this->get('/settings')->assertRedirectToRoute('login');
    }

    public function test_user_updates_basic_settings_successfully(): void
    {
        $this->actingAs($this->user);

        $updatedData = [
            'name' => 'Sara Doe',
            'email' => 'sara@doe.com',
            'username' => 'sara',
        ];

        $response = $this->patch('/settings/basic', $updatedData);

        $response->assertRedirectToRoute('settings.edit');

        $refreshedUser = User::find($this->user->id);

        $this->assertSame($updatedData['name'], $refreshedUser->name);
        $this->assertSame($updatedData['email'], $refreshedUser->email);
        $this->assertSame($updatedData['username'], $refreshedUser->username);
        $this->assertTrue($this->user->created_at->eq($refreshedUser->created_at));
        $this->assertFalse($this->oldUpdatedAt->eq($refreshedUser->updated_at));
    }

    public function test_updates_basic_settings_fails_when_email_exists(): void
    {
        $email = 'kim@example.com';
        User::factory()->create([
            'email' => $email,
        ]);
        $this->actingAs($this->user);

        $updatedData = [
            'name' => 'Sara Doe',
            'email' => $email,
            'username' => 'sara',
        ];

        $response = $this->patch('/settings/basic', $updatedData);

        $response->assertSessionHasErrors('email');

        $refreshedUser = User::find($this->user->id);

        $this->assertSame($this->user->name, $refreshedUser->name);
        $this->assertSame($this->user->email, $refreshedUser->email);
        $this->assertSame($this->user->username, $refreshedUser->username);
    }

    public function test_updates_basic_settings_fails_when_username_exists(): void
    {
        $username = 'kimdoe';
        User::factory()->create([
            'username' => $username,
        ]);
        $this->actingAs($this->user);

        $updatedData = [
            'name' => 'Sara Doe',
            'email' => 'Saradoe@example.com',
            'username' => $username,
        ];

        $response = $this->patch('/settings/basic', $updatedData);

        $response->assertSessionHasErrors('username');

        $refreshedUser = User::find($this->user->id);

        $this->assertSame($this->user->name, $refreshedUser->name);
        $this->assertSame($this->user->email, $refreshedUser->email);
        $this->assertSame($this->user->username, $refreshedUser->username);
    }

    public function test_redirects_to_login_page_when_trying_to_update_basic_setting_when_guest(): void
    {
        $this->patch('/settings/basic', [
            'name' => 'Sara Doe',
            'email' => 'sara@doe.com',
            'username' => 'sara',
        ])->assertRedirectToRoute('login');
    }

    #[DataProvider('invalidBasicSettingsDataProvider')]
    public function test_updates_basic_settings_fails(array $data, string $errorKey): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/settings/basic', $data);

        $response->assertSessionHasErrors($errorKey);

        $refreshedUser = User::find($this->user->id);

        $this->assertSame('John Doe', $refreshedUser->name);
        $this->assertSame('john@doe.com', $refreshedUser->email);
        $this->assertSame('john', $refreshedUser->username);
    }

    public static function invalidBasicSettingsDataProvider(): array
    {
        return [
            'No email' => [
                'data' => [
                    'name' => 'Sara Kim',
                    'username' => 'sara',
                ],
                'errorKey' => 'email'
            ],
            'Empty email' => [
                'data' => [
                    'email' => '',
                    'name' => 'Sara Kim',
                    'username' => 'sara',
                ],
                'errorKey' => 'email'
            ],
            'Invalid email' => [
                'data' => [
                    'email' => 'asdjkd jklll #@$$',
                    'name' => 'Sara Kim',
                    'username' => 'sara',
                ],
                'errorKey' => 'email'
            ],
            'No username' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'name' => 'Sara Kim',
                ],
                'errorKey' => 'username'
            ],
            'Empty username' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'name' => 'Sara Kim',
                    'username' => '',
                ],
                'errorKey' => 'username'
            ],
            'Small username' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'name' => 'Sara Kim',
                    'username' => 'aa',
                ],
                'errorKey' => 'username'
            ],
            'Large username' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'name' => 'Sara Kim',
                    'username' => str_repeat('a', 51),
                ],
                'errorKey' => 'username'
            ],
            'Invalid username' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'name' => 'Sara Kim',
                    'username' => 'sabri #%@#%f sdf',
                ],
                'errorKey' => 'username'
            ],
            'No name' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'username' => 'sara',
                ],
                'errorKey' => 'name'
            ],
            'Empty name' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'username' => 'sara',
                    'name' => '',
                ],
                'errorKey' => 'name'
            ],
            'Small name' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'username' => 'sara',
                    'name' => 'a',
                ],
                'errorKey' => 'name'
            ],
            'Large name' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'username' => 'sara',
                    'name' => str_repeat('a', 51),
                ],
                'errorKey' => 'name'
            ],
            'Invalid name' => [
                'data' => [
                    'email' => 'sarakim@example.com',
                    'username' => 'sara',
                    'name' => 'Sara 34 @#%^&& 2345--_',
                ],
                'errorKey' => 'name'
            ],
        ];
    }

    public function test_user_updates_password_successfully(): void
    {
        $this->actingAs($this->user);

        $updatedData = [
            'old_password' => '123456',
            'password' => 'updatedPass1234',
            'password_confirmation' => 'updatedPass1234',
        ];

        $response = $this->patch('/settings/password', $updatedData);

        $response->assertRedirectToRoute('settings.edit');

        $refreshedUser = $this->user->refresh();

        $this->assertFalse(Hash::check($updatedData['password'], $this->oldHashedPassword));
        $this->assertNotSame($updatedData['password'], $refreshedUser->password);
        $this->assertNotSame($this->oldHashedPassword, $refreshedUser->password);
    }

    public function test_redirects_to_login_page_when_trying_to_update_password_when_guest(): void
    {
        $this->patch('/settings/password', [
            'old_password' => '123456',
            'password' => 'updatedPass1234',
            'password_confirmation' => 'updatedPass1234',
        ])->assertRedirectToRoute('login');
    }

    public function test_returns_forbidden_response_when_trying_to_update_password_when_but_old_password_does_not_match(): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/settings/password', [
            'old_password' => '95i34858',
            'password' => 'updatedPass1234',
            'password_confirmation' => 'updatedPass1234',
        ]);

        $response->assertSessionHasErrors('old_password');
    }

    #[DataProvider('invalidPasswordDataProvider')]
    public function test_updates_password_fails(array $data, string $errorKey): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/settings/password', $data);

        $response->assertSessionHasErrors($errorKey);

        $refreshedUser = User::find($this->user->id);

        $this->assertSame('John Doe', $refreshedUser->name);
        $this->assertSame('john@doe.com', $refreshedUser->email);
        $this->assertSame('john', $refreshedUser->username);
    }

    public static function invalidPasswordDataProvider(): array
    {
        return [
            'No password' => [
                'data' => [
                    'old_password' => '123456',
                    'password_confirmation' => '',
                ],
                'errorKey' => 'password'
            ],
            'Empty password' => [
                'data' => [
                    'old_password' => '123456',
                    'password' => '',
                    'password_confirmation' => '',
                ],
                'errorKey' => 'password'
            ],
            'Small password' => [
                'data' => [
                    'old_password' => '123456',
                    'password' => 'dw',
                    'password_confirmation' => 'dw',
                ],
                'errorKey' => 'password'
            ],
            'Large password' => [
                'data' => [
                    'old_password' => '123456',
                    'password' => str_repeat('a', 345),
                    'password_confirmation' => str_repeat('a', 345)
                ],
                'errorKey' => 'password'
            ],
            'No password confirmation' => [
                'data' => [
                    'old_password' => '123456',
                    'password' => '532345',
                ],
                'errorKey' => 'password'
            ],
            'password confirmation does not match' => [
                'data' => [
                    'old_password' => '123456',
                    'password' => '532345',
                    'password_confirmation' => 'qqw344sddd'
                ],
                'errorKey' => 'password'
            ],
            'No old password' => [
                'data' => [
                    'password' => '123456',
                    'password_confirmation' => '123456',
                ],
                'errorKey' => 'old_password'
            ],
            'Empty old password' => [
                'data' => [
                    'password' => '123456',
                    'password_confirmation' => '123456',
                    'old_password' => '',
                ],
                'errorKey' => 'old_password'
            ],
        ];
    }
}
