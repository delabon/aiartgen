<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\FeatureTestCase;

class DeleteUserTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_deletes_user_successfully(): void
    {
        $user = User::factory()->create();
        $usersCountBefore = User::count();
        $this->actingAs($user);

        $response = $this->delete('/settings/delete-account');

        $response->assertRedirectToRoute('register.create');
        $response->assertSessionHas([
            'success' => 'Your account has been deleted.'
        ]);

        $this->assertSame(1, $usersCountBefore);
        $this->assertSame(0, User::count());
        $this->assertFalse(Auth::check());
    }

    public function test_deletes_only_the_logged_in_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $usersCountBefore = User::count();
        $this->actingAs($user1);

        $this->delete('/settings/delete-account');

        $this->assertSame(2, $usersCountBefore);
        $this->assertSame(1, User::count());
        $this->assertFalse(Auth::check());
        $this->assertSame($user2->id, User::all()[0]->id);
    }

    public function test_only_logged_in_users_can_delete_their_accounts(): void
    {
        User::factory()->create();
        User::factory()->create();
        $usersCountBefore = User::count();

        $this->delete('/settings/delete-account')
            ->assertRedirectToRoute('login');

        $this->assertSame($usersCountBefore, User::count());
    }
}
