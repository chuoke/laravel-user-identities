<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\CredentialProcessors\CustomJwtCredentialProcessor;
use Chuoke\UserIdentities\Types\Abstracts\TokenBasedIdentityType;

class CustomJwtIdentityType extends TokenBasedIdentityType
{
    public function getType(): string
    {
        return 'custom_jwt';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string'];
    }

    protected function getCredentialProcessorClass(): string
    {
        return CustomJwtCredentialProcessor::class;
    }
}
