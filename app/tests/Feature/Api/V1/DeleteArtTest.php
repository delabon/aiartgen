<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Api\V1\CreateUserAndAccessToken;

class DeleteArtTest extends TestCase
{
    use RefreshDatabase;
    use CreateUserAndAccessToken;

    private ?string $imagePath = null;

    protected function tearDown(): void
    {
        if ($this->imagePath) {
            @unlink($this->imagePath);
        }

        parent::tearDown();
    }

    public function test_deletes_art_successfully(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $art = Art::factory()->create([
            'user_id' => $user->id
        ]);
        $this->imagePath = Config::get('services.dirs.arts') . '/' . $art->filename;

        $this->assertTrue(file_exists($this->imagePath));
        $this->assertCount(1, Art::all());

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->deleteJson('/api/v1/arts/' . $art->id)
            ->assertOk();

        $this->assertCount(0, Art::all());
        $this->assertFalse(file_exists($this->imagePath));
    }

    public function test_fails_when_no_user_api_key(): void
    {
        $art = Art::factory()->create();
        $this->imagePath = Config::get('services.dirs.arts') . '/' . $art->filename;

        $this->deleteJson('/api/v1/arts/' . $art->id, )
            ->assertUnauthorized();

        $this->assertCount(1, Art::all());
    }

    public function test_fails_when_invalid_user_api_key(): void
    {
        $art = Art::factory()->create();
        $this->imagePath = Config::get('services.dirs.arts') . '/' . $art->filename;

        $this->withHeader('Authorization', 'Bearer sadjsajdjsakdkskdskdk')->deleteJson('/api/v1/arts/' . $art->id, )
            ->assertUnauthorized();

        $this->assertCount(1, Art::all());
    }

    public function test_fails_when_trying_to_delete_another_user_art(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $user2 = User::factory()->create();
        $art = Art::factory()->create([
            'user_id' => $user2->id
        ]);
        $this->imagePath = Config::get('services.dirs.arts') . '/' . $art->filename;

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->deleteJson('/api/v1/arts/' . $art->id)
            ->assertForbidden();

        $this->assertCount(1, Art::all());
        $this->assertTrue(file_exists($this->imagePath));
    }

    public function test_fails_when_exceeds_rate_limit(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $arts = Art::factory(2)->create([
            'user_id' => $user->id
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->deleteJson('/api/v1/arts/' . $arts[0]->id)
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->deleteJson('/api/v1/arts/' . $arts[1]->id)
            ->assertTooManyRequests();

        $this->assertCount(1, Art::all());
    }
}
