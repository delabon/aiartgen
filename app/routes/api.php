<?php

use App\Http\Controllers\Api\V1\ArtController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1/arts')->controller(ArtController::class)->group(function () {
    Route::get('/', 'index')->middleware('throttle:10,1');
    Route::get('/@/{user:username}', 'userArt')->where([
        'user' => '[a-z0-9\-\.]+'
    ])->middleware('throttle:10,1');
    Route::get('/{art}', 'show')->where([
        'art' => '[0-9]+'
    ])->middleware('throttle:10,1');
    Route::post('/', 'store')->middleware(['auth:sanctum', 'throttle:1,1']);
    Route::delete('/{art}', 'destroy')->middleware(['auth:sanctum'])->can('edit', 'art');
    Route::patch('/{art}', 'update')->middleware(['auth:sanctum', 'throttle:1,1'])->can('edit', 'art');
});
