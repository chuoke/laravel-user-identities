<?php

namespace Chuoke\UserIdentities\CredentialProcessors;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use SensitiveParameter;

class EncryptCredentialProcessor implements CredentialProcessorInterface
{
    public function processForStorage(#[SensitiveParameter] string $credentials): ?string
    {
        return encrypt($credentials);
    }

    public function verify(#[SensitiveParameter] string $plainCredentials, string $storedCredentials): bool
    {
        return decrypt($storedCredentials) === $plainCredentials;
    }
}
