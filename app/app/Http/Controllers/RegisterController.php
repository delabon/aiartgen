<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('register.create');
    }

    public function store(): RedirectResponse
    {
        $attributes = request()->validate([
            'email' => ['required', 'email', 'confirmed'],
            'password' => ['required', 'min:5', 'max:20', 'confirmed'],
            'name' => ['required', 'min:3', 'max:50', 'regex:/^[a-z][a-z ]+$/i'],
        ]);

        try
        {
            User::create($attributes);
        } catch (UniqueConstraintViolationException $e) {
            return to_route('register.create')->withErrors([
                'email' => 'The email address exists.'
            ]);
        }

        return to_route('login');
    }
}
