<?php

namespace App\Policies;

use App\Models\Art;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArtPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Art $art): bool
    {
        return $user->id === $art->user->id;
    }
}
