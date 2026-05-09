<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Model;

class UserIdentityFindByAuthenticatable
{
    /**
     * Find user identity by type and identifier.
     */
    public function execute(Model $authenticatable, string $type): ?UserIdentity
    {
        return UserIdentity::where('authenticatable_type', $authenticatable->getMorphClass())
            ->where('authenticatable_id', $authenticatable->getKey())
            ->where('type', $type)
            ->first();
    }
}
