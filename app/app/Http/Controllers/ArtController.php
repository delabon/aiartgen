<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiServerDownException;
use App\Exceptions\ApiServerOverloadedException;
use App\Exceptions\ImageNotFoundException;
use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\InvalidRegionException;
use App\Exceptions\RateLimitException;
use App\Models\Art;
use App\Models\User;
use App\Services\ArtGenerationApiService;
use App\Services\ImageDownloadService;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function index(): View
    {
        return view('arts.index', [
            'arts' => Art::orderBy('id', 'desc')->simplePaginate(Config::get('services.pagination.per_page')),
        ]);
    }

    public function show(Art $art): View
    {
        return view('arts.show', [
            'art' => $art,
        ]);
    }

    public function create(): View
    {
        return view('arts.create');
    }

    public function store(): RedirectResponse|Response
    {
        request()->validate([
            'prompt' => ['required', 'min:2', 'max:255'],
            'title' => ['required', 'regex:/^[a-z0-9\- ]+$/i', 'min:2', 'max:255'],
        ]);

        try {
            $artGenerationApiService = new ArtGenerationApiService(Config::get('services.openai.api_key'), new Client());
            $artUrl = $artGenerationApiService->request(
                request('prompt'),
                (int) Config::get('services.images.sizes.default.width'),
                (int) Config::get('services.images.sizes.default.height')
            );

            $imageDownloadService = new ImageDownloadService(Config::get('services.dirs.arts'));
            $artPath = $imageDownloadService->download($artUrl);

            Art::create([
                'filename' => basename($artPath),
                'title' => request('title'),
                'user_id' => Auth::user()->id,
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

    public function userArt(User $user): View
    {
        $arts = Art::with('user')
            ->where('user_id', '=', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.pagination.per_page'));

        return view('arts.user-art', [
            'arts' => $arts,
            'user' => $user
        ]);
    }

    public function edit(Art $art): View
    {
        return view('arts.edit', [
            'art' => $art
        ]);
    }

    public function update(Art $art): RedirectResponse
    {
        $attributes = request()->validate([
            'title' => ['required', 'regex:/^[a-z0-9\- ]+$/i', 'min:2', 'max:255'],
        ]);

        $art->update($attributes);

        return to_route('arts.show', [
            'art' => $art
        ]);
    }

    public function destroy(Art $art): RedirectResponse
    {
        $art->delete();

        return to_route('arts.user.art', [
            'user' => $art->user
        ]);
    }
}
