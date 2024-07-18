<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/arts')->group(function () {
    Route::get('/', [ArtController::class, 'index']);
    Route::post('/', [ArtController::class, 'store'])->middleware('auth');
    Route::get('/create', [ArtController::class, 'create'])->middleware('auth');
    Route::get('/{art}', [ArtController::class, 'show']);
});

Route::get('/image/{art}', [ImageController::class, 'show']);

Route::get('/login', function () {
    return 'Login page';
})->name('login');
