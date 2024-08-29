<?php

namespace Tests\Feature;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\FeatureTestCase;

class ListArtsTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $this->get('/arts')
            ->assertOk()
            ->assertViewIs('arts.index');
    }

    public function test_contains_correct_arts(): void
    {
        $arts = Art::factory(3)->create();

        $response = $this->get('/arts');

        $response->assertOk();

        foreach ($arts as $art) {
            $response->assertSee(route('image.show', ['art' => $art]));
        }
    }

    public function test_paginates_arts_in_desc_order_correctly(): void
    {
        Config::set('services.pagination.per_page', 2);
        $artUrls = [];
        $artistUrls = [];
        $artistNames = [];
        $arts = Art::factory(3)->create();
        $response = $this->get('/arts');

        $response->assertOk();

        for ($i = 2; $i > 0; $i--) {
            $artUrls[] = route('image.show', ['art' => $arts[$i]]);
            $artistUrls[] = route('arts.user.art', ['user' => $arts[$i]->user]);
            $artistNames[] = $arts[$i]->user->name;
        }

        $response->assertSeeInOrder($artUrls);
        $response->assertSeeInOrder($artistUrls);
        $response->assertSeeTextInOrder($artistNames);

        $response->assertDontSee(route('image.show', ['art' => $arts[0]]));

        $response->assertSee('arts?page=2');
    }

    public function test_contains_no_art_text_when_no_art(): void
    {
        $response = $this->get('/arts');

        $response->assertOk()
            ->assertSee('No art at the moment.');
    }
}
