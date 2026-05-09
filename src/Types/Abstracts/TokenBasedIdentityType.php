<?php

namespace Chuoke\UserIdentities\Types\Abstracts;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;

abstract class TokenBasedIdentityType implements IdentityTypeInterface
{
    public function requiresCredential(): bool
    {
        return true;
    }

    public function isDefaultVerified(): bool
    {
        return true; // Token-based identities are typically pre-verified
    }

    /**
     * Get base validation rules for token-based identities.
     */
    protected function getBaseValidationRules(): array
    {
        return [
            'credentials' => ['required', 'string'],
        ];
    }

    /**
     * Get identifier validation rules.
     */
    abstract protected function getIdentifierRules(): array;

    /**
     * Get credential processor for this token type.
     */
    abstract protected function getCredentialProcessorClass(): string;

    public function getCredentialProcessor(): CredentialProcessorInterface
    {
        $processorClass = $this->getCredentialProcessorClass();

        return new $processorClass();
    }

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
