<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Exceptions\DuplicateIdentityException;
use Chuoke\UserIdentities\Models\UserIdentity;

class UserIdentityUniquenessCheck
{
    /**
     * Check if the identity is unique.
     *
     * @throws DuplicateIdentityException
     */
    public function execute(
        string $authenticatableType,
        int $authenticatableId,
        string $type,
        string $identifier,
        ?int $existingIdentityId = null
    ): void {
        $existingIdentity = UserIdentity::where('authenticatable_type', $authenticatableType)
            ->where('authenticatable_id', $authenticatableId)
            ->where('type', $type)
            ->where('identifier', $identifier)
            ->when($existingIdentityId, fn ($query) => $query->where('id', '!=', $existingIdentityId))
            ->first();

        if ($existingIdentity) {
            throw DuplicateIdentityException::make($type, $identifier);
        }
    }
}
