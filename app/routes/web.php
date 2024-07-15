<?php

use App\Http\Controllers\ArtController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/arts', [ArtController::class, 'create']);
