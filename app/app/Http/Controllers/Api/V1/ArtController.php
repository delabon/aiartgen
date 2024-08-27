<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArtCollection;
use App\Http\Resources\V1\ArtResource;
use App\Models\Art;
use App\Models\User;
use App\Services\V1\SortService;
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
}
