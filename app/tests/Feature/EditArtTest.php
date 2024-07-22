<?php

namespace Tests\Feature;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EditArtTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_edits_art_successfully(): void
    {
        $updatedTitle = 'My updated title';
        $user = User::factory()->create();
        $this->actingAs($user);

        $art = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
            'updated_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
        ]);

        $response = $this->patch('/arts/' . $art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertRedirectToRoute('arts.show', [
            'art' => $art
        ]);

        $updatedArt = Art::find($art->id);

        $this->assertNotSame($art->title, $updatedArt->title);
        $this->assertSame($updatedTitle, $updatedArt->title);
        $this->assertTrue($art->created_at->eq($updatedArt->created_at));
        $this->assertFalse($art->updated_at->eq($updatedArt->updated_at));
    }

    public function test_redirects_to_login_page_when_trying_to_edit_art_when_guest(): void
    {
        $updatedTitle = 'My updated title';
        $user = User::factory()->create();

        $art = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
            'updated_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
        ]);

        $response = $this->patch('/arts/' . $art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertRedirectToRoute('login');

        $refreshedArt = Art::find($art->id);

        $this->assertSame($art->title, $refreshedArt->title);
        $this->assertTrue($art->updated_at->eq($refreshedArt->updated_at));
    }

    public function test_editing_art_fails_when_not_the_owner(): void
    {
        $updatedTitle = 'My updated title';
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $art = Art::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
            'updated_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
        ]);

        $this->actingAs($user2);

        $response = $this->patch('/arts/' . $art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertForbidden();

        $refreshedArt = Art::find($art->id);

        $this->assertSame($art->title, $refreshedArt->title);
        $this->assertTrue($art->updated_at->eq($refreshedArt->updated_at));
    }

    #[DataProvider('invalidDataProvider')]
    public function test_user_editing_art_fails_on(array $data): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $art = Art::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patch('/arts/' . $art->id, $data);

        $response->assertSessionHasErrors('title');

        $refreshedArt = Art::find($art->id);

        $this->assertSame($art->title, $refreshedArt->title);
        $this->assertTrue($art->created_at->eq($refreshedArt->created_at));
        $this->assertTrue($art->updated_at->eq($refreshedArt->updated_at));
    }

    public static function invalidDataProvider(): array
    {
        return [
            'No title' => [
                'data' => []
            ],
            'Empty title' => [
                'data' => [
                    'title' => ''
                ]
            ],
            'Small title' => [
                'data' => [
                    'title' => 'a'
                ]
            ],
            'Large title' => [
                'data' => [
                    'title' => str_repeat('a', 257)
                ]
            ],
            'non alpha, numeric space, and dash title' => [
                'data' => [
                    'title' => 'My cool title <#$%!@!#%&*',
                ]
            ],
        ];
    }
}
