<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('/arts')->controller(ArtController::class)->group(function () {
    Route::get('/', 'index')->name('arts.index');
    Route::post('/', 'store')->middleware('auth');
    Route::get('/create', 'create')->middleware('auth');
    Route::get('/{art}', 'show');
});

Route::get('/image/{art}', [ImageController::class, 'show']);

Route::prefix('/login')->controller(LoginController::class)->group(function () {
    Route::get('/', 'create')->name('login');
    Route::post('/', 'store')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
