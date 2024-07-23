<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit');
    }

    public function updateBasic(): RedirectResponse
    {
        $attributes = request()->validate([
            'name' => ['required', 'min:3', 'max:50', 'regex:/^[a-z][a-z ]+$/i'],
            'username' => ['required', 'min:3', 'max:50', 'regex:/^[a-z0-9\_]+$/', Rule::unique('users')->ignore(Auth::user()->username)],
            'email' => ['required', 'email', Rule::unique('users')->ignore(Auth::user()->email)],
        ]);

        Auth::user()->update($attributes);

        return to_route('settings.edit');
    }

    public function updatePassword(): RedirectResponse
    {
        $attributes = request()->validate([
            'old_password' => ['required'],
            'password' => ['required', 'min:5', 'max:20', 'confirmed']
        ]);

        if (!Hash::check($attributes['old_password'], Auth::user()->password)) {
            return to_route('settings.edit')->withErrors([
                'old_password' => 'The old password does not match.'
            ]);
        }

        Auth::user()->update([
            'password' => $attributes['password']
        ]);

        return to_route('settings.edit');
    }
}
