<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\CredentialProcessors\PlainCredentialProcessor;
use Chuoke\UserIdentities\Types\Abstracts\TokenBasedIdentityType;

class JwtIdentityType extends TokenBasedIdentityType
{
    public function getType(): string
    {
        return 'jwt';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string'];
    }

    protected function getCredentialProcessorClass(): string
    {
        return PlainCredentialProcessor::class;
    }
}
