<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('register.create');
    }

    public function store(): RedirectResponse
    {
        $attributes = request()->validate([
            'email' => ['required', 'email', 'confirmed', 'unique:users'],
            'password' => ['required', 'min:5', 'max:20', 'confirmed'],
            'name' => ['required', 'min:3', 'max:50', 'regex:/^[a-z][a-z ]+$/i'],
            'username' => ['required', 'min:3', 'max:50', 'regex:/^[a-z0-9\_]+$/', 'unique:users']
        ]);

        User::create($attributes);

        return to_route('login');
    }
}
