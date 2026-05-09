<?php

namespace Chuoke\UserIdentities\CredentialProcessors;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use SensitiveParameter;

class PlainCredentialProcessor implements CredentialProcessorInterface
{
    public function processForStorage(#[SensitiveParameter] string $credentials): ?string
    {
        return $credentials;
    }

    public function verify(#[SensitiveParameter] string $plainCredentials, string $storedCredentials): bool
    {
        return $storedCredentials === $plainCredentials;
    }
}
