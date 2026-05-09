<?php

namespace Chuoke\UserIdentities\Types\Abstracts;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use Chuoke\UserIdentities\CredentialProcessors\HashCredentialProcessor;

abstract class PasswordBasedIdentityType implements IdentityTypeInterface
{
    public function requiresCredential(): bool
    {
        return true;
    }

    public function getCredentialProcessor(): CredentialProcessorInterface
    {
        return new HashCredentialProcessor();
    }

    public function isDefaultVerified(): bool
    {
        return false; // Password-based identities typically require verification
    }

    /**
     * Get base validation rules for password-based identities.
     */
    protected function getBaseValidationRules(): array
    {
        return [
            'credentials' => ['required', 'string', 'min:8'],
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
