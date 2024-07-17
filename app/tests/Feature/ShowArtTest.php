<?php

namespace Tests\Feature;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowArtTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $art = Art::factory()->create();

        $this->get('/arts/' . $art->id)
            ->assertOk()
            ->assertViewIs('arts.show');
    }

    public function test_contains_correct_art(): void
    {
        $imageBaseUrl = url('/image');
        $artTitle = 'Amazing Art';
        $art = Art::factory()->create([
            'title' => $artTitle
        ]);

        $response = $this->get('/arts/' . $art->id);

        $response->assertOk();
        $response->assertSee($imageBaseUrl . '/' . $art->id);
        $response->assertSee('/artist/' . $art->user->id);
        $response->assertSeeText($art->user->name);
        $response->assertSeeText($artTitle);
    }

    public function test_returns_not_found_response_when_id_does_not_exist(): void
    {
        $response = $this->get('/arts/893892394');

        $response->assertNotFound();
    }
}
