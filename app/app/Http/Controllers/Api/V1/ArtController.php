<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiServerDownException;
use App\Exceptions\ApiServerOverloadedException;
use App\Exceptions\ImageNotFoundException;
use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\InvalidRegionException;
use App\Exceptions\RateLimitException;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArtCollection;
use App\Http\Resources\V1\ArtResource;
use App\Models\Art;
use App\Models\User;
use App\Services\ArtGenerationApiService;
use App\Services\ImageDownloadService;
use App\Services\V1\SortService;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function __construct(private readonly SortService $sortService)
    {
    }

    public function index(): ArtCollection
    {
        return new ArtCollection(Art::with('user')->orderBy('created_at', $this->sortService->getDirection())->paginate(Config::get('services.api.v1.pagination.per_page')));
    }

    public function userArt(User $user): ArtCollection
    {
        return new ArtCollection(Art::with('user')->where('user_id', $user->id)->orderBy('created_at', $this->sortService->getDirection())->paginate(Config::get('services.api.v1.pagination.per_page')));
    }

    public function show(Art $art): ArtResource
    {
        return new ArtResource($art->load('user'));
    }

    public function store(): ArtResource|JsonResponse
    {
        $validated = request()->validate([
            'title' => ['required', 'min:2', 'max:255', 'regex:/^[a-z0-9\- ]+$/i'],
            'prompt' => ['required', 'min:2', 'max:255']
        ]);

        try {
            $artGenerationApiService = new ArtGenerationApiService(Config::get('services.openai.api_key'), new Client());
            $artUrl = $artGenerationApiService->request(
                $validated['prompt'],
                (int) Config::get('services.images.sizes.default.width'),
                (int) Config::get('services.images.sizes.default.height')
            );

            $imageDownloadService = new ImageDownloadService(Config::get('services.dirs.arts'));
            $artPath = $imageDownloadService->download($artUrl);

            $art = Art::create([
                'filename' => basename($artPath),
                'title' => $validated['title'],
                'user_id' => request()->user()->id,
            ]);

            return new ArtResource($art->load('user'));
        } catch (InvalidApiKeyException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (ApiServerOverloadedException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (InvalidRegionException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (RateLimitException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
        } catch (ImageNotFoundException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (InvalidImageException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        } catch (ApiServerDownException|GuzzleException|ErrorException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
