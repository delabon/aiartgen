<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\FeatureTestCase;
use Tests\Traits\Api\V1\ArtUtils;

class ListUserArtTest extends FeatureTestCase
{
    use RefreshDatabase;
    use ArtUtils;

    private const BASE_ENDPOINT = '/api/v1/arts/@/';

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

        $response = $this->getJson(self::BASE_ENDPOINT . $users[0]->username)
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(1, $responseData['data']);

        $this->assertArtLoop($responseData['data'], $date);
        $this->assertSame($users[0]->id, $responseData['data'][0]['artist']['id']);
    }

    public function test_returns_empty_data_when_user_has_no_art(): void
    {
        $users = User::factory(2)->create();

        Art::factory()->create([
            'user_id' => $users[0]->id,
        ]);

        $response = $this->getJson(self::BASE_ENDPOINT . $users[1]->username)
            ->assertOk();

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

        $response = $this->getJson(self::BASE_ENDPOINT . $user->username)
            ->assertOk();

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

        $response = $this->getJson(self::BASE_ENDPOINT . $user->username . '?sort=oldest')
            ->assertOk();

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

        $response = $this->getJson(self::BASE_ENDPOINT . $user->username . '?sort=newest')
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art2->id);
        $this->assertSame($responseData['data'][1]['id'], $art1->id);
    }

    public function test_returns_too_many_requests_response_when_guest_exceeds_rate_limit(): void
    {
        $art = Art::factory()->create();
        $uri = self::BASE_ENDPOINT . $art->user->username;

        for ($i = 0; $i < 11; $i++) {
            $this->getJson($uri);
        }

        $this->getJson($uri)
            ->assertTooManyRequests();
    }

    public function test_returns_not_found_response_when_username_does_not_exist(): void
    {
        $this->getJson(self::BASE_ENDPOINT . 'asdjsjad98ds9sd0sad0dsa9da')
            ->assertNotFound();
    }
}
