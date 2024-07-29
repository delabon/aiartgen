<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function store(): RedirectResponse
    {
        $attributes = request()->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $user = User::findByEmail($attributes['email']);

        $token = Password::createToken($user);

        $user->sendPasswordResetNotification($token);

        return to_route('login')->with('success', 'An email with the reset link has been sent to your email address.');
    }

    public function create(): View
    {
        return view('password-reset.create');
    }

    public function edit(string $token, User $user): View
    {
        if (!Password::tokenExists($user, $token)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('password-reset.edit', [
            'user' => $user,
            'token' => $token
        ]);
    }

    public function update(User $user): RedirectResponse
    {
        $attributes = request()->validate([
            'reset_password_token' => ['required', 'string'],
            'password' => ['required', 'min:5', 'max:20', 'confirmed']
        ]);

        if (!Password::tokenExists($user, $attributes['reset_password_token'])) {
            return to_route('password.reset.edit', ['user' => $user, 'token' => $attributes['reset_password_token']])->withErrors([
                'token' => 'The token does not exist or is not associated with the user.'
            ]);
        }

        $user->update([
            'password' => $attributes['password'],
        ]);

        Password::deleteToken($user);

        return to_route('login')->with('success', 'Your password has been updated.');
    }
}
