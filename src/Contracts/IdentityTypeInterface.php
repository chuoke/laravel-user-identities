<?php

namespace Chuoke\UserIdentities\Contracts;

interface IdentityTypeInterface
{
    public function getType(): string;

    public function requiresCredential(): bool;

    public function getCredentialProcessor(): CredentialProcessorInterface;

    public function isDefaultVerified(): bool;

    public function getValidationRules(): array;
}
