<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArtCollection;
use App\Models\Art;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function index(): ArtCollection
    {
        return new ArtCollection(Art::with('user')->orderBy('created_at', 'DESC')->paginate(Config::get('services.api.v1.pagination.per_page')));
    }
}
