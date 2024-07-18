<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/arts')->controller(ArtController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store')->middleware('auth');
    Route::get('/create', 'create')->middleware('auth');
    Route::get('/{art}', 'show');
});

Route::get('/image/{art}', [ImageController::class, 'show']);

Route::get('/login', function () {
    return 'Login page';
})->name('login');
