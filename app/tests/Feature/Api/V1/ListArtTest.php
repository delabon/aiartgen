<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Api\V1\ArtUtils;

class ListArtTest extends TestCase
{
    use RefreshDatabase;
    use ArtUtils;

    private const BASE_ENDPOINT = '/api/v1/arts';

    public function test_lists_art_successfully(): void
    {
        $date = now()->subYear();
        Art::factory(2)->create([
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $response = $this->getJson(self::BASE_ENDPOINT)
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(2, $responseData['data']);

        $this->assertArtLoop($responseData['data'], $date);
    }

    public function test_returns_empty_data_when_no_art(): void
    {
        $response = $this->getJson(self::BASE_ENDPOINT)
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(0, $responseData['data']);
    }

    public function test_paginates_art_successfully(): void
    {
        Config::set('services.api.v1.pagination.per_page', 2);
        Art::factory()->create([
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()
        ]);
        $art3 = Art::factory()->create([
            'created_at' => now()->subYear()
        ]);

        $response = $this->getJson(self::BASE_ENDPOINT)
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

    public function test_returns_too_many_requests_response_when_guest_exceeds_rate_limit(): void
    {
        Art::factory()->create();

        for ($i = 0; $i < 11; $i++) {
            $this->getJson(self::BASE_ENDPOINT);
        }

        $this->getJson(self::BASE_ENDPOINT)
            ->assertTooManyRequests();
    }

    public function test_lists_art_in_ascending_order_successfully(): void
    {
        $art1 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()
        ]);

        $response = $this->getJson(self::BASE_ENDPOINT . '?sort=oldest')
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art1->id);
        $this->assertSame($responseData['data'][1]['id'], $art2->id);
    }

    public function test_lists_art_in_descending_order_successfully(): void
    {
        $art1 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()
        ]);

        $response = $this->getJson(self::BASE_ENDPOINT . '?sort=newest')
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art2->id);
        $this->assertSame($responseData['data'][1]['id'], $art1->id);
    }
}
