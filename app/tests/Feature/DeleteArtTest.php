<?php

namespace Tests\Feature;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeleteArtTest extends TestCase
{
    use RefreshDatabase;

    private ?User $user;
    private ?Art $art;
    private ?string $filename;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->art = Art::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->filename = $this->art->filename;
    }

    protected function tearDown(): void
    {
        $this->user = null;
        $this->art = null;

        parent::tearDown();
    }

    public function test_user_deletes_art_successfully(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete('/arts/' . $this->art->id);

        $response->assertRedirectToRoute('arts.user.art', [
            'user' => $this->user
        ]);
        $response->assertSessionHas('success', 'Your art has been deleted.');

        $this->assertCount(0, Art::all());
        $this->assertFalse(file_exists(Config::get('services.dirs.arts') . '/' . $this->filename));
    }

    public function test_returns_not_found_response_when_art_does_not_exist(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete('/arts/' . 92349);

        $response->assertNotFound();
    }

    public function test_redirects_to_login_when_guest(): void
    {
        $this->delete('/arts/' . $this->art->id)
            ->assertRedirectToRoute('login');

        $this->assertCount(1, Art::all());
    }

    public function test_returns_forbidden_response_when_not_the_owner(): void
    {
        $user2 = User::factory()->create();
        $this->actingAs($user2);

        $this->delete('/arts/' . $this->art->id)
            ->assertForbidden();

        $this->assertCount(1, Art::all());
    }
}
