<?php

namespace Chuoke\UserIdentities\CredentialProcessors;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use SensitiveParameter;

class CustomJwtCredentialProcessor implements CredentialProcessorInterface
{
    public function processForStorage(#[SensitiveParameter] string $credentials): ?string
    {
        // Custom JWT processing logic
        $parts = explode('.', $credentials);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid JWT format');
        }

        // Store processed JWT
        return 'jwt_'.base64_encode($credentials);
    }

    public function verify(#[SensitiveParameter] string $plainCredentials, string $storedCredentials): bool
    {
        try {
            // Validate JWT format
            $parts = explode('.', $plainCredentials);
            if (count($parts) !== 3) {
                return false;
            }

            // Compare processed values
            $processedInput = $this->processForStorage($plainCredentials);

            return $processedInput === $storedCredentials;
        } catch (\Exception $e) {
            return false;
        }
    }
}
