<?php

namespace Chuoke\UserIdentities;

use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use SensitiveParameter;

class CredentialProcessor
{
    /**
     * Process credentials for storage using the identity type's processor.
     */
    public static function process(
        IdentityTypeInterface $typeConfig,
        #[SensitiveParameter] string $credentials
    ): ?string {
        if (! $typeConfig->requiresCredential()) {
            return null;
        }

        $processor = $typeConfig->getCredentialProcessor();

        return $processor->processForStorage($credentials);
    }

    /**
     * Verify credentials using the identity type's processor.
     */
    public static function verify(
        IdentityTypeInterface $typeConfig,
        #[SensitiveParameter] string $plainCredentials,
        string $storedCredentials
    ): bool {
        if (! $typeConfig->requiresCredential()) {
            return false;
        }

        $processor = $typeConfig->getCredentialProcessor();

        return $processor->verify($plainCredentials, $storedCredentials);
    }
}
