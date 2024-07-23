<?php

namespace Tests\Feature;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $artTitle = 'Amazing Art';
        $art = Art::factory()->create([
            'title' => $artTitle
        ]);

        $response = $this->get('/arts/' . $art->id);

        $response->assertOk();
        $response->assertSee(route('image.show', ['art' => $art]));
        $response->assertSee(route('arts.user.art', ['user' => $art->user]));
        $response->assertSeeText($art->user->name);
        $response->assertSeeText($artTitle);
    }

    public function test_returns_not_found_response_when_id_does_not_exist(): void
    {
        $response = $this->get('/arts/893892394');

        $response->assertNotFound();
    }
}
