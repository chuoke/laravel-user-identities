<?php

namespace Chuoke\UserIdentities\Concerns;

use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasIdentities
{
    /**
     * Get all identities for the authenticatable entity.
     *
     * @return MorphMany<UserIdentity, $this>
     */
    public function identities(): MorphMany
    {
        return $this->morphMany(UserIdentity::class, 'authenticatable');
    }

    /**
     * Get verified identities only.
     *
     * @return MorphMany<UserIdentity, $this>
     */
    public function verifiedIdentities(): MorphMany
    {
        return $this->identities()->whereNotNull('verified_at');
    }

    /**
     * Get all unverified identities.
     */
    public function unverifiedIdentities(): MorphMany
    {
        return $this->identities()->whereNull('verified_at');
    }

    /**
     * Get a specific identity by type.
     */
    public function getIdentity(string $type): ?UserIdentity
    {
        return $this->identities()->where('type', $type)->first();
    }

    /**
     * Get a specific identity by type and identifier.
     */
    public function getIdentityBy(string $type, string $identifier): ?UserIdentity
    {
        return $this->identities()
            ->where('type', $type)
            ->where('identifier', $identifier)
            ->first();
    }

    /**
     * Get all identities by type.
     */
    public function getIdentitiesByType(string $type): Collection
    {
        return $this->identities()->where('type', $type)->get();
    }

    /**
     * Check if the entity has a specific identity type.
     */
    public function hasIdentity(string $type): bool
    {
        return $this->identities()->where('type', $type)->exists();
    }

    /**
     * Check if model has verified identity of specific type.
     */
    public function hasVerifiedIdentity(string $type): bool
    {
        return $this->verifiedIdentities()->where('type', $type)->exists();
    }

    /**
     * Get all verified identities.
     */
    public function getVerifiedIdentities(): Collection
    {
        return $this->verifiedIdentities()->get();
    }

    /**
     * Get primary identity (first verified identity, or first identity if none verified).
     */
    public function getPrimaryIdentity(): ?UserIdentity
    {
        return $this->verifiedIdentities()->first() ?? $this->identities()->first();
    }

    /**
     * Get identity types the model has.
     */
    public function getIdentityTypes(): array
    {
        return $this->identities()->pluck('type')->unique()->values()->toArray();
    }

    /**
     * Get verified identity types the model has.
     */
    public function getVerifiedIdentityTypes(): array
    {
        return $this->verifiedIdentities()->pluck('type')->unique()->values()->toArray();
    }
}
