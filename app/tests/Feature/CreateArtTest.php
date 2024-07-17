<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\ProvidesInvalidApiKeys;

class CreateArtTest extends TestCase
{
    use RefreshDatabase;
    use ProvidesInvalidApiKeys;

    public function test_redirects_to_login_page_when_not_logged_in(): void
    {
        $this->get('/arts/create')->assertRedirect('login');
    }

    public function test_returns_correct_view(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/arts/create')
            ->assertOk()
            ->assertViewIs('arts.create');
    }

    public function test_user_generates_art_successfully(): void
    {
        $artTitle = 'A cat that sings';
        $dir = env('APP_ART_GEN_DIR');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/arts', [
            'prompt' => 'Make art about a cat singer.',
            'title' => $artTitle
        ]);

        $response->assertRedirect('/arts');

        $art = Art::first();
        $filePath = $dir . '/' . $art->filename;

        $this->assertInstanceOf(Art::class, $art);
        $this->assertCount(1, $art->user->arts);
        $this->assertSame($user->id, $art->user->id);
        $this->assertTrue(file_exists($filePath));
        $this->assertSame('image/png', mime_content_type($filePath));
        $this->assertSame($artTitle, $art->title);
    }

    public function test_redirects_to_login_page_when_trying_to_create_art_when_not_logged_in(): void
    {
        $response = $this->post('/arts', [
            'prompt' => 'Make art about happy dogs.',
            'title' => 'Dancing pets',
        ]);

        $response->assertRedirect('login');
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_returns_unauthorized_response_when_no_api_key(string $apiKey): void
    {
        Config::set('services.openai.api_key', $apiKey);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/arts', [
            'prompt' => 'Make art about happy dogs.',
            'title' => 'Dancing pets',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertCount(0, Art::all());
    }

    #[DataProvider('invalidDataProvider')]
    public function test_returns_bad_request_when_invalid_data(array $data, string $sessionKey): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/arts', $data);

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertSessionHasErrors([$sessionKey]);
        $this->assertCount(0, Art::all());
    }

    public static function invalidDataProvider(): array
    {
        return [
            'No prompt' => [
                'data' => [
                    'title' => 'Cool rabbits',
                ],
                'sessionKey' => 'prompt'
            ],
            'Empty prompt' => [
                'data' => [
                    'prompt' => '',
                    'title' => 'Cool rabbits',
                ],
                'sessionKey' => 'prompt'
            ],
            'Large prompt' => [
                'data' => [
                    'prompt' => str_repeat('a', 345),
                    'title' => 'Cool rabbits',
                ],
                'sessionKey' => 'prompt'
            ],
            'Small prompt' => [
                'data' => [
                    'prompt' => 'a',
                    'title' => 'Cool rabbits',
                ],
                'sessionKey' => 'prompt'
            ],
            'No title' => [
                'data' => [
                    'prompt' => 'Hello there',
                ],
                'sessionKey' => 'title'
            ],
            'Empty title' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'title' => '',
                ],
                'sessionKey' => 'title'
            ],
            'non alpha, numeric space, and dash title' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'title' => 'My cool title <#$%!@!#%&*',
                ],
                'sessionKey' => 'title'
            ],
            'Large title' => [
                'data' => [
                    'prompt' => 'Cool rabbits',
                    'title' => str_repeat('a', 345),
                ],
                'sessionKey' => 'title'
            ],
            'Small title' => [
                'data' => [
                    'prompt' => 'Cool rabbits',
                    'title' => 'a',
                ],
                'sessionKey' => 'title'
            ],
        ];
    }
}
