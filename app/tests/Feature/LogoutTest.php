<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_out_user_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete('/logout');

        $response->assertRedirectToRoute('home');
    }
}
