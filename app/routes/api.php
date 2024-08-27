<?php

use App\Http\Controllers\Api\V1\ArtController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/art', [ArtController::class, 'index'])->middleware('throttle:10,1');
Route::get('/v1/art/@/{user:username}', [ArtController::class, 'userArt'])->where([
    'user' => '[a-z0-9\-\.]+'
])->middleware('throttle:10,1');
