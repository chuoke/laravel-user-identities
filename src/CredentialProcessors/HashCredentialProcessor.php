<?php

namespace Chuoke\UserIdentities\CredentialProcessors;

use Chuoke\UserIdentities\Contracts\CredentialProcessorInterface;
use Illuminate\Support\Facades\Hash;
use SensitiveParameter;

class HashCredentialProcessor implements CredentialProcessorInterface
{
    public function processForStorage(#[SensitiveParameter] string $credentials): ?string
    {
        return Hash::make($credentials);
    }

    public function verify(#[SensitiveParameter] string $plainCredentials, string $storedCredentials): bool
    {
        return Hash::check($plainCredentials, $storedCredentials);
    }
}
