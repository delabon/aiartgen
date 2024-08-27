<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArtCollection;
use App\Models\Art;
use App\Models\User;
use App\Services\V1\SortService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function __construct(private SortService $sortService)
    {
    }

    public function index(Request $request): ArtCollection
    {
        return new ArtCollection(Art::with('user')->orderBy('created_at', $this->sortService->getDirection())->paginate(Config::get('services.api.v1.pagination.per_page')));
    }

    public function userArt(User $user, Request $request): ArtCollection
    {
        return new ArtCollection(Art::with('user')->where('user_id', $user->id)->orderBy('created_at', $this->sortService->getDirection())->paginate(Config::get('services.api.v1.pagination.per_page')));
    }
}
