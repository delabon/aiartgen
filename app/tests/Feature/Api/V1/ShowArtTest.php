<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Api\V1\ArtUtils;

class ShowArtTest extends TestCase
{
    use RefreshDatabase;
    use ArtUtils;

    public function test_returns_art_successfully(): void
    {
        $date = now()->subMonth();
        $art = Art::factory([
            'created_at' => $date,
            'updated_at' => $date,
        ])->create();

        $response = $this->getJson('/api/v1/art/' . $art->id)
            ->assertOk();

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);

        $this->assertArt($responseData['data'], $date);
    }

    public function test_returns_not_found_when_id_does_not_exist(): void
    {
        $this->getJson('/api/v1/art/32423423')
            ->assertNotFound();
    }

    public function test_returns_too_many_requests_response_when_exceeds_rate_limit(): void
    {
        $art = Art::factory()->create();
        $uri = '/api/v1/art/' . $art->id;

        for ($i = 0; $i < 11; $i++) {
            $this->getJson($uri);
        }

        $this->getJson($uri)
            ->assertTooManyRequests();
    }
}
