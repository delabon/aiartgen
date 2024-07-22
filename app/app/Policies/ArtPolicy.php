<?php

namespace App\Policies;

use App\Models\Art;
use App\Models\User;

class ArtPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function edit(User $user, Art $art): bool
    {
        return $art->user->is($user);
    }
}
