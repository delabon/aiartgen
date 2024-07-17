<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/arts', [ArtController::class, 'index']);
Route::post('/arts', [ArtController::class, 'create']);

Route::get('/image/{art}', [ImageController::class, 'show']);
