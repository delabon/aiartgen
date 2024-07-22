<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('/arts')->name('arts.')->controller(ArtController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->middleware('auth');
    Route::get('/create', 'create')->name('create')->middleware('auth');
    Route::get('/{art}/edit', 'edit')->name('edit')->middleware('auth')->can('edit', 'art');
    Route::patch('/{art}', 'update')->name('update')->middleware('auth')->can('edit', 'art');
    Route::delete('/{art}', 'destroy')->name('destroy')->middleware('auth')->can('edit', 'art');
    Route::get('/@/{user:username}', 'userArt')->name('user.art');
    Route::get('/{art}', 'show')->name('show');
});

Route::get('/image/{art}', [ImageController::class, 'show'])->name('image.show');

Route::prefix('/login')->controller(LoginController::class)->group(function () {
    Route::get('/', 'create')->name('login');
    Route::post('/', 'store')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::prefix('/register')->name('register.')->controller(RegisterController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store');
});
