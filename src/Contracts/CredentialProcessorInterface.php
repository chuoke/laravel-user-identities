<?php

namespace Chuoke\UserIdentities\Contracts;

interface CredentialProcessorInterface
{
    /**
     * Process credentials for storage.
     */
    public function processForStorage(string $credentials): ?string;

    /**
     * Verify credentials against stored value.
     */
    public function verify(string $plainCredentials, string $storedCredentials): bool;
}
