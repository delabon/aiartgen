<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(): RedirectResponse
    {
        try {
            $attributes = request()->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'min:5', 'max:20']
            ]);
        } catch (ValidationException $e) {
            return to_route('login')->withErrors($e->errors());
        }

        if (!Auth::attempt($attributes)) {
            return to_route('login')->withErrors([
                'email' => 'Invalid login credentials.'
            ]);
        }

        return to_route('home');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        return to_route('home');
    }
}
