<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiServerDownException;
use App\Exceptions\ApiServerOverloadedException;
use App\Exceptions\ImageNotFoundException;
use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\InvalidRegionException;
use App\Exceptions\RateLimitException;
use App\Models\Art;
use App\Services\ArtGenerationApiService;
use App\Services\ImageDownloadService;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function create(): RedirectResponse|Response
    {
        request()->validate([
            'prompt' => ['required', 'min:2', 'max:255'],
            'width' => ['required', 'numeric', 'in:256,512,1024'],
            'height' => ['required', 'numeric', 'in:256,512,1024'],
        ]);

        try {
            $artGenerationApiService = new ArtGenerationApiService(Config::get('services.openai.api_key'), new Client());
            $artUrl = $artGenerationApiService->request(
                request('prompt'),
                (int)request('width'),
                (int)request('height')
            );

            $imageDownloadService = new ImageDownloadService(Config::get('services.dirs.arts'));
            $artPath = $imageDownloadService->download($artUrl);

            Art::create([
                'name' => basename($artPath),
                'user_id' => Auth::user()->id
            ]);

            return redirect('/arts');
        } catch (InvalidApiKeyException $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (ApiServerOverloadedException $e) {
            return new Response($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (InvalidRegionException $e) {
            return new Response($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (RateLimitException $e) {
            return new Response($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
        } catch (ImageNotFoundException $e) {
            return new Response($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (InvalidImageException $e) {
            return new Response($e->getMessage(), Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        } catch (ApiServerDownException|GuzzleException|ErrorException $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
