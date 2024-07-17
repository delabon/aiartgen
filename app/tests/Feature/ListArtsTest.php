<?php

namespace Tests\Feature;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ListArtsTest extends TestCase
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
        $imageBaseUrl = url('/image');

        $arts = Art::factory(3)->create();

        $response = $this->get('/arts');

        $response->assertOk();

        foreach ($arts as $art) {
            $response->assertSee($imageBaseUrl . '/' . $art->id);
        }
    }

    public function test_paginates_arts_in_desc_order_correctly(): void
    {
        Config::set('services.pagination.per_page', 2);
        $imageBaseUrl = url('/image');
        $artUrls = [];
        $arts = Art::factory(3)->create();

        $response = $this->get('/arts');

        $response->assertOk();

        for ($i = 2; $i > 0; $i--) {
            $artUrls[] = $imageBaseUrl . '/' . $arts[$i]->id;
        }

        $response->assertSeeInOrder($artUrls);

        $response->assertDontSee($imageBaseUrl . '/' . $arts[0]->id);

        $response->assertSee('arts?page=2');
    }
}
