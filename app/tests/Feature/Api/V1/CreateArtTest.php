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
        $user = User::factory()->create();
        $token = $user->createToken('user-token', ['manage-user-art', 'manager-user-account']);

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

    #[DataProvider('InvalidDataProvider')]
    public function test_fails_when_invalid_data(array $data, string $errorMessage, bool $createUser = true): void
    {
        if ($createUser) {
            $user = User::factory()->create();
            $data['userId'] = $user->id;
        }

        $response = $this->postJson('/api/v1/arts', $data)->assertUnprocessable();

        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($responseData['message'], $errorMessage);
        $this->assertCount(0, Art::all());
    }

    public static function InvalidDataProvider(): array
    {
        return [
            'No user id' => [
                'data' => [
                    'title' => 'Beautiful electric cats',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The user id field is required.',
                'createUser' => false,
            ],
            'Invalid user id' => [
                'data' => [
                    'userId' => 'asjjkjmas234',
                    'title' => 'Beautiful electric cats',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The user id field must be an integer.',
                'createUser' => false,
            ],
            'User id does not exist' => [
                'data' => [
                    'userId' => 324893284,
                    'title' => 'Beautiful electric cats',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The selected user id is invalid.',
                'createUser' => false,
            ],
            'No title' => [
                'data' => [
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field is required.',
                'createUser' => true,
            ],
            'Small title' => [
                'data' => [
                    'title' => 'a',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field must be at least 2 characters.',
                'createUser' => true,
            ],
            'Large title' => [
                'data' => [
                    'title' => str_repeat('a', 277),
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field must not be greater than 255 characters.',
                'createUser' => true,
            ],
            'Invalid title' => [
                'data' => [
                    'title' => 'sad324 #@$%%324"smdk 009.?/,.',
                    'prompt' => 'Make art about beautiful electric cats',
                ],
                'errorMessage' => 'The title field format is invalid.',
                'createUser' => true,
            ],
            'No prompt' => [
                'data' => [
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field is required.',
                'createUser' => true,
            ],
            'Small prompt' => [
                'data' => [
                    'prompt' => 'q',
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field must be at least 2 characters.',
                'createUser' => true,
            ],
            'Large prompt' => [
                'data' => [
                    'prompt' => str_repeat('a', 434),
                    'title' => 'Beautiful electric cats',
                ],
                'errorMessage' => 'The prompt field must not be greater than 255 characters.',
                'createUser' => true,
            ],
        ];
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_fails_when_invalid_openai_api_key(string $apiKey): void
    {
        Config::set('services.openai.api_key', $apiKey);

        $user = User::factory()->create();

        $this->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertUnauthorized();

        $this->assertCount(0, Art::all());
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

    public function test_fails_when_exceeds_rate_limit(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token', ['manage-user-art', 'manager-user-account']);

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertCreated();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->postJson('/api/v1/arts', [
            'title' => 'Beautiful electric cats',
            'prompt' => 'Make art about beautiful electric cats',
            'userId' => $user->id,
        ])->assertTooManyRequests();
    }
}
