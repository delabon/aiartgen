<?php

namespace Tests\Feature;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ListUserArtTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $user = User::factory()->create();

        $this->get('/arts/@/' . $user->username)
            ->assertOk()
            ->assertViewIs('arts.user-art');
    }

    public function test_paginates_art_in_descending_order_correctly(): void
    {
        Config::set('services.pagination.per_page', 2);
        $user = User::factory()->create();
        $arts = Art::factory(3)->create([
            'user_id' => $user->id
        ]);
        $artImages = [];
        $artUrls = [];

        for ($i = 2; $i > 0; $i--) {
            $artImages[] = route('image.show', ['art' => $arts[$i]]);
            $artUrls[] = route('arts.show', ['art' => $arts[$i]]);
        }

        $response = $this->get('/arts/@/' . $user->username);

        $response->assertSeeInOrder($artImages);
        $response->assertSeeInOrder($artUrls);
        $response->assertDontSee(route('image.show', ['art' => $arts[0]]));
        $response->assertDontSee(route('arts.show', ['art' => $arts[0]]));
        $response->assertSee(route('arts.user.art', ['user' => $user]) . '?page=2');
    }

    public function test_contains_no_art_when_user_has_no_art(): void
    {
        $user = User::factory()->create();

        $response = $this->get('/arts/@/' . $user->username);

        $response->assertSeeText('No art at the moment.');
        $response->assertDontSee(route('arts.user.art', ['user' => $user]) . '?page=2');
    }
}
