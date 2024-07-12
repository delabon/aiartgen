<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiServerDownException;
use App\Exceptions\ApiServerOverloadedException;
use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidRegionException;
use App\Exceptions\RateLimitException;
use App\Services\ArtGenerationApiService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;
use UnexpectedValueException;

class ArtGenerationApiServiceTest extends TestCase
{
    public function test_requests_an_art_successfully(): void
    {
        $apiKey = 'asdasdasddad';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $streamInterfaceMock = $this->createMock(StreamInterface::class);
        $streamInterfaceMock->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode([
                'created' => 1720694980,
                'data' => [
                    [
                        'revised_prompt' => 'An amusing image of a variety of dogs dancing. A Rottweiler, a Poodle, and a Beagle all standing on their hind legs, moving rhythmically as if dancing. The atmosphere is festive with multi-colored bunting flags and balloons floating in the air. There is a disco ball hanging from the center, scattering glittering lights around as it rotates. The dogs all have happy expressions on their faces. Each dog is wearing a collar with a tag hanging, showing the outline of a musical note to depict their love for music and dance.',
                        'url' => 'https://example.test/image.png'
                    ]
                ]
            ]));

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamInterfaceMock);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $result = $artGenerationApiService->request($prompt, $width, $height);

        $this->assertIsString($result);
        $this->assertUrl($result);
    }

    #[DataProvider('invalidApiKeyDataProvider')]
    public function test_throws_invalid_api_key_exception_when_invalid_api_key(string $apiKey): void
    {
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(\Illuminate\Http\Response::HTTP_UNAUTHORIZED);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(InvalidApiKeyException::class);
        $this->expectExceptionMessage('Invalid API Key.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public static function invalidApiKeyDataProvider (): array
    {
        return [
            'Empty api key' => [
                'apiKey' => ''
            ],
            'Invalid api key' => [
                'apiKey' => 'invalid apu key'
            ],
        ];
    }

    public function test_throws_invalid_region_exception_when_invalid_region(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(\Illuminate\Http\Response::HTTP_FORBIDDEN);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(InvalidRegionException::class);
        $this->expectExceptionMessage('Country, region, or territory not supported.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public function test_throws_rate_limit_exception_when_rate_limit_reached(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(\Illuminate\Http\Response::HTTP_TOO_MANY_REQUESTS);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit reached for requests.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public function test_throws_server_error_exception_when_api_server_is_down(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(ApiServerDownException::class);
        $this->expectExceptionMessage('The API server had an error while processing your request.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public function test_throws_server_overloaded_exception_when_api_server_is_overloaded(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(\Illuminate\Http\Response::HTTP_SERVICE_UNAVAILABLE);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(ApiServerOverloadedException::class);
        $this->expectExceptionMessage('The API server is currently overloaded, please try again later');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public function test_throws_unexpected_value_exception_when_api_server_returns_invalid_json(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $streamInterfaceMock = $this->createMock(StreamInterface::class);
        $streamInterfaceMock->expects($this->once())
            ->method('getContents')
            ->willReturn('"');

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamInterfaceMock);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The json returned from the API server is invalid.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    public function test_throws_unexpected_value_exception_when_api_server_returns_unexpected_json(): void
    {
        $apiKey = 'Good API Key';
        $prompt = 'Music band of cats';
        $width = 1024;
        $height = 1024;

        $streamInterfaceMock = $this->createMock(StreamInterface::class);
        $streamInterfaceMock->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode([
                'foo' => 'bar'
            ]));

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamInterfaceMock);

        $clientMock = $this->getClientMock($apiKey, $prompt, $width, $height, $responseMock);

        $artGenerationApiService = new ArtGenerationApiService($apiKey, $clientMock);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The json returned from the API server is invalid.');

        $artGenerationApiService->request($prompt, $width, $height);
    }

    /**
     * @param string $apiKey
     * @param string $prompt
     * @param int $width
     * @param int $height
     * @param object $responseMock
     * @return Client
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function getClientMock(
        string $apiKey,
        string $prompt,
        int $width,
        int $height,
        object $responseMock
    ): Client {
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openai.com/v1/images/generations',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey
                    ],
                    'json' => [
                        "model" => "dall-e-3",
                        "prompt" => $prompt,
                        "n" => 1,
                        "size" => $width . "x" . $height
                    ],
                ]
            )->willReturn($responseMock);
        return $clientMock;
    }
}
