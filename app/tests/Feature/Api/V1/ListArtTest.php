<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ListArtTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_art_successfully(): void
    {
        $date = now()->subYear();
        Art::factory(2)->create([
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $response = $this->get('/api/v1/art');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(2, $responseData['data']);

        foreach ($responseData['data'] as $art) {
            $this->assertArrayHasKey('id', $art);
            $this->assertArrayHasKey('title', $art);
            $this->assertArrayHasKey('artist', $art);
            $this->assertArrayHasKey('createdAt', $art);
            $this->assertArrayHasKey('updatedAt', $art);
            $this->assertArrayHasKey('url', $art);
            $this->assertArrayNotHasKey('filename', $art);
            $this->assertUrl($art['url']);
            $this->assertSame($date->format('Y-m-d H:i:s'), $art['createdAt']);
            $this->assertSame($date->format('Y-m-d H:i:s'), $art['updatedAt']);
            $this->assertIsArray($art['artist']);
            $this->assertArrayHasKey('id', $art['artist']);
            $this->assertArrayHasKey('name', $art['artist']);
            $this->assertArrayHasKey('username', $art['artist']);
            $this->assertArrayNotHasKey('password', $art['artist']);
            $this->assertArrayNotHasKey('created_at', $art['artist']);
            $this->assertArrayNotHasKey('updated_at', $art['artist']);
            $this->assertArrayNotHasKey('email', $art['artist']);
        }
    }

    public function test_returns_empty_data_when_no_art(): void
    {
        $response = $this->get('/api/v1/art');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
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

        $response = $this->get('/api/v1/art');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
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
            $this->get('/api/v1/art');
        }

        $response = $this->get('/api/v1/art');

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function test_lists_art_in_ascending_order_successfully(): void
    {
        $art1 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()->subYear()
        ]);
        $art2 = Art::factory()->create([
            'created_at' => now()->subYear()->subYear()
        ]);

        $response = $this->get('/api/v1/art?order=oldest');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
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

        $response = $this->get('/api/v1/art?order=newest');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['data'][0]['id'], $art2->id);
        $this->assertSame($responseData['data'][1]['id'], $art1->id);
    }
}
