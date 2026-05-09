<?php

namespace Chuoke\UserIdentities\Types\Abstracts;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use Chuoke\UserIdentities\CredentialProcessors\PlainCredentialProcessor;

abstract class OAuthBasedIdentityType implements IdentityTypeInterface
{
    public function requiresCredential(): bool
    {
        return true;
    }

    public function getCredentialProcessor(): CredentialProcessorInterface
    {
        return new PlainCredentialProcessor(); // OAuth tokens are stored as plain text
    }

    public function isDefaultVerified(): bool
    {
        return true; // OAuth identities are pre-verified
    }

    /**
     * Get base validation rules for OAuth-based identities.
     */
    protected function getBaseValidationRules(): array
    {
        return [
            'credentials' => ['required'], // OAuth token
        ];
    }

    /**
     * Get identifier validation rules.
     */
    abstract protected function getIdentifierRules(): array;

    public function getValidationRules(): array
    {
        return array_merge(
            $this->getBaseValidationRules(),
            [
                'identifier' => $this->getIdentifierRules(),
            ]
        );
    }

    abstract public function getType(): string;
}
