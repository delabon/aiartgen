<?php

namespace Tests\Integration\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_findByEmail_method_returns_correct_user(): void
    {
        $name = 'John Doe';
        $username = 'john.doe';
        $email = 'john@doe.fr';
        User::factory()->create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
        ]);

        $user = User::findByEmail($email);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->id);
        $this->assertSame($email, $user->email);
        $this->assertSame($name, $user->name);
        $this->assertSame($username, $user->username);
    }

    public function test_findByEmail_method_returns_null_when_email_does_not_exist(): void
    {
        $this->assertNull(User::findByEmail('john@doe.fr'));
    }
}
