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

class ArtGenTest extends TestCase
{
    use RefreshDatabase;
    use ProvidesInvalidApiKeys;

    public function test_user_generates_art_successfully(): void
    {
        $dir = env('APP_ART_GEN_DIR');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/arts', [
            'prompt' => 'Make art about a cat singer.',
            'width' => 1024,
            'height' => 1024,
        ]);

        $response->assertRedirect('/arts');

        $art = Art::first();
        $filePath = $dir . '/' . $art->name;

        $this->assertInstanceOf(Art::class, $art);
        $this->assertCount(1, $art->user->arts);
        $this->assertSame($user->id, $art->user->id);
        $this->assertTrue(file_exists($filePath));
        $this->assertSame('image/png', mime_content_type($filePath));
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_returns_unauthorized_response_when_no_api_key(string $apiKey): void
    {
        Config::set('services.openai.api_key', $apiKey);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/arts', [
            'prompt' => 'Make art about happy dogs.',
            'width' => 1024,
            'height' => 1024,
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
                    'width' => 1024,
                    'height' => 1024,
                ],
                'sessionKey' => 'prompt'
            ],
            'Empty prompt' => [
                'data' => [
                    'prompt' => '',
                    'width' => 1024,
                    'height' => 1024,
                ],
                'sessionKey' => 'prompt'
            ],
            'Large prompt' => [
                'data' => [
                    'prompt' => str_repeat('a', 345),
                    'width' => 1024,
                    'height' => 1024,
                ],
                'sessionKey' => 'prompt'
            ],
            'No width' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'height' => 1024,
                ],
                'sessionKey' => 'width'
            ],
            'Empty width' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => '',
                    'height' => 1024,
                ],
                'sessionKey' => 'width'
            ],
            'Non number width' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => 'five',
                    'height' => 1024,
                ],
                'sessionKey' => 'width'
            ],
            'Width not 256, 516, or 1024' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => 128,
                    'height' => 1024,
                ],
                'sessionKey' => 'width'
            ],
            'No height' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => 1024,
                ],
                'sessionKey' => 'height'
            ],
            'Empty height' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'height' => '',
                    'width' => 1024,
                ],
                'sessionKey' => 'height'
            ],
            'Non number height' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => 1024,
                    'height' => true,
                ],
                'sessionKey' => 'height'
            ],
            'Height not 256, 516, or 1024' => [
                'data' => [
                    'prompt' => 'Hello there',
                    'width' => 1024,
                    'height' => 64,
                ],
                'sessionKey' => 'height'
            ],
        ];
    }
}
