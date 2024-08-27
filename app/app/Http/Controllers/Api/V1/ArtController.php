<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArtCollection;
use App\Models\Art;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ArtController extends Controller
{
    public function index(Request $request): ArtCollection
    {
        $orderBy = in_array($request->query('order'), ['oldest', 'newest']) ? $request->query('order') : 'newest';
        $orderBy = $orderBy === 'newest' ? 'DESC' : 'ASC';

        return new ArtCollection(Art::with('user')->orderBy('created_at', $orderBy)->paginate(Config::get('services.api.v1.pagination.per_page')));
    }
}
