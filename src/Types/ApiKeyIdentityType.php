<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\CredentialProcessors\EncryptCredentialProcessor;
use Chuoke\UserIdentities\Types\Abstracts\TokenBasedIdentityType;

class ApiKeyIdentityType extends TokenBasedIdentityType
{
    public function getType(): string
    {
        return 'api_key';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string'];
    }

    protected function getCredentialProcessorClass(): string
    {
        return EncryptCredentialProcessor::class;
    }
}
