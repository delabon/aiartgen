<?php

use App\Http\Controllers\Api\V1\ArtController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/art', [ArtController::class, 'index'])->middleware('throttle:10,1');
