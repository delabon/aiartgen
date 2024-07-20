<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('/arts')->controller(ArtController::class)->group(function () {
    Route::get('/', 'index')->name('arts.index');
    Route::post('/', 'store')->middleware('auth');
    Route::get('/create', 'create')->middleware('auth');
    Route::get('/@/{user:username}', 'userArt')->name('arts.user.art');
    Route::get('/{art}', 'show')->name('arts.show');
});

Route::get('/image/{art}', [ImageController::class, 'show'])->name('image.show');

Route::prefix('/login')->controller(LoginController::class)->group(function () {
    Route::get('/', 'create')->name('login');
    Route::post('/', 'store')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::prefix('/register')->controller(RegisterController::class)->group(function () {
    Route::get('/', 'create')->name('register.create');
    Route::post('/', 'store')->name('register.store');
});
