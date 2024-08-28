<?php

namespace Tests\Traits\Api\V1;

use App\Models\User;

trait CreateUserAndAccessToken
{
    /**
     * @return array
     */
    protected function createUserAndAccessToken(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token', ['manage-user-art', 'manager-user-account']);

        return array($user, $token);
    }
}
