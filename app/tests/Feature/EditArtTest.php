<?php

namespace Tests\Feature;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EditArtTest extends TestCase
{
    use RefreshDatabase;

    private ?User $user;
    private ?Art $art;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->art = Art::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
            'updated_at' => Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto'),
        ]);
    }

    protected function tearDown(): void
    {
        $this->user = null;
        $this->art = null;

        parent::tearDown();
    }

    public function test_redirects_to_login_page_when_not_logged_in(): void
    {
        $this->get('/arts/' . $this->art->id . '/edit')->assertRedirect('login');
    }

    public function test_returns_correct_view(): void
    {
        $this->actingAs($this->user);

        $this->get('/arts/' . $this->art->id . '/edit')
            ->assertOk()
            ->assertViewIs('arts.edit');
    }

    public function test_returns_forbidden_response_when_trying_to_access_edit_page_when_not_owner(): void
    {
        $user2 = User::factory()->create();
        $this->actingAs($user2);

        $this->get('/arts/' . $this->art->id . '/edit')
            ->assertForbidden();
    }

    public function test_returns_not_found_response_when_trying_to_access_edit_page_when_art_does_not_exist(): void
    {
        $this->actingAs($this->user);

        $this->get('/arts/' . 94359859 . '/edit')
            ->assertNotFound();
    }

    public function test_user_edits_art_successfully(): void
    {
        $updatedTitle = 'My updated title';
        $this->actingAs($this->user);

        $response = $this->patch('/arts/' . $this->art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertRedirectToRoute('arts.show', [
            'art' => $this->art
        ]);
        $response->assertSessionHas('success', 'Your art has been updated.');

        $updatedArt = Art::find($this->art->id);

        $this->assertNotSame($this->art->title, $updatedArt->title);
        $this->assertSame($updatedTitle, $updatedArt->title);
        $this->assertTrue($this->art->created_at->eq($updatedArt->created_at));
        $this->assertFalse($this->art->updated_at->eq($updatedArt->updated_at));
    }

    public function test_redirects_to_login_page_when_trying_to_edit_art_when_guest(): void
    {
        $updatedTitle = 'My updated title';

        $response = $this->patch('/arts/' . $this->art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertRedirectToRoute('login');

        $refreshedArt = Art::find($this->art->id);

        $this->assertSame($this->art->title, $refreshedArt->title);
        $this->assertTrue($this->art->updated_at->eq($refreshedArt->updated_at));
    }

    public function test_returns_not_found_response_when_trying_to_edit_non_existent_art(): void
    {
        $this->actingAs($this->user);

        $this->patch('/arts/' . 4566456, [
            'title' => 'My updated title'
        ])->assertNotFound();
    }

    public function test_editing_art_fails_when_not_the_owner(): void
    {
        $updatedTitle = 'My updated title';
        $user2 = User::factory()->create();

        $this->actingAs($user2);

        $response = $this->patch('/arts/' . $this->art->id, [
            'title' => $updatedTitle
        ]);

        $response->assertForbidden();

        $refreshedArt = Art::find($this->art->id);

        $this->assertSame($this->art->title, $refreshedArt->title);
        $this->assertTrue($this->art->updated_at->eq($refreshedArt->updated_at));
    }

    #[DataProvider('invalidDataProvider')]
    public function test_user_editing_art_fails_on(array $data): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/arts/' . $this->art->id, $data);

        $response->assertSessionHasErrors('title');

        $refreshedArt = Art::find($this->art->id);

        $this->assertSame($this->art->title, $refreshedArt->title);
        $this->assertTrue($this->art->created_at->eq($refreshedArt->created_at));
        $this->assertTrue($this->art->updated_at->eq($refreshedArt->updated_at));
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
