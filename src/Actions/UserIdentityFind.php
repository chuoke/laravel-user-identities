<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Models\UserIdentity;

class UserIdentityFind
{
    /**
     * Find user identity by type and identifier.
     */
    public function execute(
        string $identifier,
        ?string $type = null,
        ?string $authenticatableType = null,
        ?int $authenticatableId = null
    ): ?UserIdentity {
        return UserIdentity::where('identifier', $identifier)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($authenticatableType, fn ($query) => $query->where('authenticatable_type', $authenticatableType))
            ->when($authenticatableId, fn ($query) => $query->where('authenticatable_id', $authenticatableId))
            ->first();
    }
}
