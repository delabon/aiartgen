<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Api\V1\ArtUtils;
use Tests\Traits\ProvidesInvalidApiKeys;

class CreateArtTest extends TestCase
{
    use RefreshDatabase;
    use ArtUtils;
    use ProvidesInvalidApiKeys;

    public function test_creates_art_successfully(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertCreated();

        $responseData = json_decode($response->getContent(), true);

        $this->assertArt($responseData['data'], now());
        $this->assertSame($responseData['data']['artist']['id'], $user->id);
        $this->assertCount(1, Art::all());
    }

    public function test_fails_when_no_user_api_key(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertUnauthorized();

        $this->assertCount(0, Art::all());
    }

    public function test_fails_when_invalid_user_api_key(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ], headers: [
            'Authorization' => 'Bearer: 238482349klklkdfllf'
        ])->assertUnauthorized();

        $this->assertCount(0, Art::all());
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_fails_when_invalid_openai_api_key(string $apiKey): void
    {
        Config::set('services.openai.api_key', $apiKey);

        list($user, $token) = $this->createUserAndAccessToken();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertUnauthorized();

        $this->assertCount(0, Art::all());
    }

    #[DataProvider('InvalidDataProvider')]
    public function test_fails_when_invalid_data(array $data, string $errorMessage): void
    {
        list($user, $token) = $this->createUserAndAccessToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/v1/arts', $data)
            ->assertUnprocessable();

        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($responseData['message'], $errorMessage);
        $this->assertCount(0, Art::all());
    }

    public static function InvalidDataProvider(): array
    {
        return [
            'No title' => [
                'data' => [
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field is required.',
            ],
            'Small title' => [
                'data' => [
                    'title' => 'a',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field must be at least 2 characters.',
            ],
            'Large title' => [
                'data' => [
                    'title' => str_repeat('a', 277),
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field must not be greater than 255 characters.',
            ],
            'Invalid title' => [
                'data' => [
                    'title' => 'sad324 #@$%%324"smdk 009.?/,.',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field format is invalid.',
            ],
            'No prompt' => [
                'data' => [
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field is required.',
            ],
            'Small prompt' => [
                'data' => [
                    'prompt' => 'q',
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field must be at least 2 characters.',
            ],
            'Large prompt' => [
                'data' => [
                    'prompt' => str_repeat('a', 434),
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field must not be greater than 255 characters.',
            ],
        ];
    }

    public function test_fails_when_exceeds_rate_limit(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertCreated();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertTooManyRequests();
    }

    /**
     * @return array
     */
    protected function createUserAndAccessToken(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token', ['manage-user-art', 'manager-user-account']);

        return array($user, $token);
    }
}
