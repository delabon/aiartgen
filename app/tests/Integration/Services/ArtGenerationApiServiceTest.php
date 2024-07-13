<?php

namespace Tests\Integration\Services;

use App\Exceptions\InvalidApiKeyException;
use App\Services\ArtGenerationApiService;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\ProvidesInvalidApiKeys;

class ArtGenerationApiServiceTest extends TestCase
{
    use ProvidesInvalidApiKeys;

    private string $artGeneratedPath = '';

    protected function tearDown(): void
    {
        @unlink($this->artGeneratedPath);

        parent::tearDown();
    }

    public function test_requests_an_art_successfully(): void
    {
        $artGenerationApiService = new ArtGenerationApiService(
            env('APP_OPENAI_API_KEY'),
            new Client()
        );

        $result = $artGenerationApiService->request('Dancing dogs.', 1024, 1024);

        $this->assertIsString($result);
        $this->assertUrl($result);
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_throws_invalid_api_key_exception_when_invalid_api_key(string $apiKey): void
    {
        $artGenerationApiService = new ArtGenerationApiService(
            $apiKey,
            new Client()
        );

        $this->expectException(InvalidApiKeyException::class);
        $this->expectExceptionMessage('Invalid API Key.');

        $artGenerationApiService->request('Dancing dogs.', 1024, 1024);
    }
}
