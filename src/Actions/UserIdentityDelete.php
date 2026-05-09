<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Models\UserIdentity;

class UserIdentityDelete
{
    /**
     * Delete a user identity.
     *
     * @param  bool  $force  Whether to force delete (bypass soft deletes)
     */
    public function execute(UserIdentity $identity, bool $force = false): bool
    {
        if ($force) {
            return $identity->forceDelete();
        }

        return $identity->delete();
    }
}
