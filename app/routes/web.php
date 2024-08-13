<?php

use App\Http\Controllers\ArtController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

Route::delete('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::prefix('/register')->name('register.')->controller(RegisterController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store');
});

Route::prefix('/password-reset')->name('password.reset.')->controller(PasswordResetController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/send', 'store')->name('store');
    Route::get('/{token}-{user}', 'edit')->name('edit')->where([
        'user' => '[0-9]+'
    ]);
    Route::patch('/{user}', 'update')->name('update')->where([
        'user' => '[0-9]+'
    ]);
});

Route::prefix('/settings')->name('settings.')->controller(SettingsController::class)->group(function () {
    Route::get('/', 'edit')->name('edit')->middleware('auth');
    Route::patch('/basic', 'updateBasic')->name('update.basic')->middleware('auth');
    Route::patch('/password', 'updatePassword')->name('update.password')->middleware('auth');
    Route::delete('/delete-account', 'destroy')->name('destroy')->middleware('auth');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify')->where([
    'id' => '[0-9]+',
]);
