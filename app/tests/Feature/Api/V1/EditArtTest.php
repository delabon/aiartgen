<?php

namespace Tests\Feature\Api\V1;

use App\Models\Art;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Api\V1\CreateUserAndAccessToken;

class EditArtTest extends TestCase
{
    use RefreshDatabase;
    use CreateUserAndAccessToken;

    public function test_edits_art_successfully(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $art = Art::factory()->create([
            'user_id' => $user->id
        ]);
        $filename = $art->filename;
        $title = 'Super Awesome Title';

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->patchJson('/api/v1/arts/' . $art->id, [
            'title' => $title,
            'filename' => 'bla bla bla',
        ])->assertOk();

        $art->refresh();

        $this->assertSame($title, $art->title);
        $this->assertSame($filename, $art->filename);
    }

    public function test_fails_when_art_id_does_not_exist(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->patchJson('/api/v1/arts/843784', [
            'title' => 'Bla Bla Bla',
        ])->assertNotFound();
    }

    public function test_fails_when_no_user_api_key(): void
    {
        $art = Art::factory()->create();

        $this->patchJson('/api/v1/arts/' . $art->id, [
            'title' => 'Bla Bla Bla',
        ])->assertUnauthorized();
    }

    public function test_fails_when_invalid_user_api_key(): void
    {
        $art = Art::factory()->create();

        $this->withHeader('Authorization', 'Bearer lkjasdsa98dsaklsaddo9')->patchJson('/api/v1/arts/' . $art->id, [
            'title' => 'Bla Bla Bla',
        ])->assertUnauthorized();
    }

    public function test_fails_when_user_is_trying_to_update_non_owned_art(): void
    {
        $art = Art::factory()->create();
        $title = $art->title;
        list($user, $token) = $this->createUserAndAccessToken();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->patchJson('/api/v1/arts/' . $art->id, [
            'title' => 'Bla Bla Bla',
        ])->assertForbidden();

        $art->refresh();

        $this->assertSame($title, $art->title);
    }

    #[DataProvider('InvalidDataProvider')]
    public function test_fails_when_invalid_data(array $data, string $errorMessage): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $art = Art::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->patchJson('/api/v1/arts/' . $art->id, $data)
            ->assertUnprocessable();

        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($responseData['message'], $errorMessage);
    }

    public static function InvalidDataProvider(): array
    {
        return [
            'No title' => [
                'data' => [],
                'errorMessage' => 'The title field is required.'
            ],
            'Empty title' => [
                'data' => [
                    'title' => ''
                ],
                'errorMessage' => 'The title field is required.'
            ],
            'Small title' => [
                'data' => [
                    'title' => 'a'
                ],
                'errorMessage' => 'The title field must be at least 2 characters.'
            ],
            'Large title' => [
                'data' => [
                    'title' => str_repeat('a', 555),
                ],
                'errorMessage' => 'The title field must not be greater than 255 characters.'
            ],
            'Invalid title' => [
                'data' => [
                    'title' => 'salkska #$23099"/?.4324^&%%^jkjkdsf878f90',
                ],
                'errorMessage' => 'The title field format is invalid.'
            ],
        ];
    }

    public function test_fails_when_exceeds_rate_limit(): void
    {
        list($user, $token) = $this->createUserAndAccessToken();
        $art = Art::factory()->create([
            'user_id' => $user->id
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->patchJson('/api/v1/arts/' . $art->id, [
            'title' => 'Bla Bla Bla',
        ])->assertOk();

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)->patchJson('/api/v1/arts/' . $art->id, [
            'title' => 'My Test',
        ])->assertTooManyRequests();
    }
}
