<?php

namespace App\Services;

use App\Exceptions\ApiServerDownException;
use App\Exceptions\ApiServerOverloadedException;
use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidRegionException;
use App\Exceptions\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use UnexpectedValueException;

class ArtGenerationApiService
{
    public function __construct(
        private readonly string $openaiApiKey,
        private readonly Client $client
    ) {
    }

    /**
     * @param string $prompt
     * @param int $width
     * @param int $height
     * @return string
     * @throws ApiServerDownException
     * @throws ApiServerOverloadedException
     * @throws GuzzleException
     * @throws InvalidApiKeyException
     * @throws InvalidRegionException
     * @throws RateLimitException
     */
    public function request(string $prompt, int $width, int $height): string
    {
        $response = $this->client->post('https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey
            ],
            'json' => [
                "model" => "dall-e-3",
                "prompt" => $prompt,
                "n" => 1,
                "size" => $width . "x" . $height
            ],
        ]);

        $json = match ($response->getStatusCode()) {
            Response::HTTP_UNAUTHORIZED => throw new InvalidApiKeyException('Invalid API Key.'),
            Response::HTTP_FORBIDDEN => throw new InvalidRegionException('Country, region, or territory not supported.'),
            Response::HTTP_TOO_MANY_REQUESTS => throw new RateLimitException('Rate limit reached for requests.'),
            Response::HTTP_INTERNAL_SERVER_ERROR => throw new ApiServerDownException('The API server had an error while processing your request.'),
            Response::HTTP_SERVICE_UNAVAILABLE => throw new ApiServerOverloadedException('The API server is currently overloaded, please try again later.'),
            default => json_decode($response->getBody()->getContents(), true)
        };

        if (!$json || !isset($json['data'], $json['data'][0], $json['data'][0]['url'])) {
            throw new UnexpectedValueException('The json returned from the API server is invalid.');
        }

        return $json['data'][0]['url'];
    }
}
