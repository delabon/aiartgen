<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    public function verify(int $id, string $hash): RedirectResponse
    {
        $user = User::FindOrFail($id);
        $expectedHash = sha1($user->email);

        if (hash_equals($expectedHash, $hash)) {
            $user->markEmailAsVerified();
            $user->save();

            return to_route('login')->with('success', 'Your email has been verified.');
        }

        return to_route('home')->with('error', 'Invalid verification link.');
    }
}
