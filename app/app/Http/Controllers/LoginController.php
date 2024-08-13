<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('login.create');
    }

    public function store(): RedirectResponse
    {
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:5', 'max:20']
        ]);

        if (!Auth::attempt($attributes)) {
            return to_route('login')->withErrors([
                'email' => 'Invalid login credentials.'
            ]);
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            Auth::logout();

            return to_route('login')->with('error', 'You need to verify your email address.');
        }

        session()->regenerate();
        session()->flash('success', 'You have signed-in successfully.');

        return to_route('home');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        session()->flash('success', 'You have signed-out successfully.');

        return to_route('home');
    }
}
