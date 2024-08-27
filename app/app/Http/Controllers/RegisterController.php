<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

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

        /** @var User $user */
        $user = User::create($attributes);
        $user->createToken('user-token', ['manage-user-art', 'manager-user-account']);

        Notification::send($user, new VerifyEmail());

        session()->flash('success', 'Your account has been created.');

        return to_route('login');
    }
}
