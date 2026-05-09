<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Models\UserIdentity;

class UserIdentityVerify
{
    /**
     * Verify a user identity.
     */
    public function execute(UserIdentity $identity): bool
    {
        if ($identity->isVerified()) {
            return true;
        }

        return $identity->markAsVerified();
    }
}
