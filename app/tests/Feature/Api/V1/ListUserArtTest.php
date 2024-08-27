<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Api\V1\ArtUtils;

class ListUserArtTest extends TestCase
{
    use RefreshDatabase;
    use ArtUtils;

    public function test_lists_user_art_successfully(): void
    {
        $users = User::factory(2)->create();
        $date = now()->subYear();
        Art::factory()->create([
            'created_at' => $date,
            'updated_at' => $date,
            'user_id' => $users[0]->id,
        ]);
        Art::factory()->create([
            'created_at' => $date,
            'updated_at' => $date,
            'user_id' => $users[1]->id,
        ]);

        $response = $this->get('/api/v1/art/@/' . $users[0]->username);

        $response->assertStatus(Response::HTTP_OK)
            ->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(1, $responseData['data']);

        $this->assertArt($responseData['data'], $date);
        $this->assertSame($users[0]->id, $responseData['data'][0]['artist']['id']);
    }

    public function test_returns_empty_data_when_user_has_no_art(): void
    {
        $users = User::factory(2)->create();

        Art::factory()->create([
            'user_id' => $users[0]->id,
        ]);

        $response = $this->get('/api/v1/art/@/' . $users[1]->username);

        $response->assertStatus(Response::HTTP_OK)->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(0, $responseData['data']);
    }

    public function test_paginates_user_art_successfully(): void
    {
        Config::set('services.api.v1.pagination.per_page', 2);
        $user = User::factory()->create();
        Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()
        ]);
        $art3 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()
        ]);

        $response = $this->get('/api/v1/art/@/' . $user->username);

        $response->assertStatus(Response::HTTP_OK)
            ->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertSame($art3->id, $responseData['data'][0]['id']);
        $this->assertSame($art2->id, $responseData['data'][1]['id']);
        $this->assertArrayHasKey('links', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertArrayHasKey('current_page', $responseData['meta']);
        $this->assertArrayHasKey('last_page', $responseData['meta']);
        $this->assertSame(1, $responseData['meta']['current_page']);
        $this->assertSame(2, $responseData['meta']['last_page']);
    }

    public function test_lists_user_art_in_ascending_order_successfully(): void
    {
        $user = User::factory()->create();
        $art1 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()
        ]);

        $response = $this->get('/api/v1/art/@/' . $user->username . '?order=oldest');

        $response->assertStatus(Response::HTTP_OK)
            ->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art1->id);
        $this->assertSame($responseData['data'][1]['id'], $art2->id);
    }

    public function test_lists_user_art_in_descending_order_successfully(): void
    {
        $user = User::factory()->create();
        $art1 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subYear()->subYear()
        ]);

        $response = $this->get('/api/v1/art/@/' . $user->username . '?order=newest');

        $response->assertStatus(Response::HTTP_OK)
            ->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art2->id);
        $this->assertSame($responseData['data'][1]['id'], $art1->id);
    }

    public function test_returns_too_many_requests_response_when_guest_exceeds_rate_limit(): void
    {
        $art = Art::factory()->create();
        $uri = '/api/v1/art/@/' . $art->user->username;

        for ($i = 0; $i < 11; $i++) {
            $this->get($uri);
        }

        $response = $this->get($uri);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function test_returns_not_found_response_when_username_does_not_exist(): void
    {
        $response = $this->get('/api/v1/art/@/asdjsjad98ds9sd0sad0dsa9da');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
