<?php

namespace Chuoke\UserIdentities\Dtos;

class UserIdentityUpdateData
{
    public function __construct(
        public readonly ?string $identifier = null,
        public readonly ?string $credentials = null,
        public readonly ?bool $verified = null,
    ) {
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            identifier: $data['identifier'] ?? null,
            credentials: $data['credentials'] ?? null,
            verified: $data['verified'] ?? null,
        );
    }

    /**
     * Check if has identifier to update.
     */
    public function hasIdentifier(): bool
    {
        return $this->identifier !== null;
    }

    /**
     * Check if has credentials to update.
     */
    public function hasCredentials(): bool
    {
        return $this->credentials !== null;
    }

    /**
     * Get only non-null values as array.
     */
    public function toNonNullArray(): array
    {
        return array_filter([
            'identifier' => $this->identifier,
            'credentials' => $this->credentials,
            'verified' => $this->verified,
        ], fn ($value) => $value !== null);
    }

    /**
     * Create for identifier update only.
     */
    public static function forIdentifier(string $identifier, ?bool $verified = null): self
    {
        return new self(identifier: $identifier, verified: $verified);
    }

    /**
     * Create for credentials update only.
     */
    public static function forCredentials(string $credentials): self
    {
        return new self(credentials: $credentials);
    }
}
