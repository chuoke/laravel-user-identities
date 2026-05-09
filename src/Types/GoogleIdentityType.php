<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\OAuthBasedIdentityType;

class GoogleIdentityType extends OAuthBasedIdentityType
{
    public function getType(): string
    {
        return 'google';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string', 'email'];
    }
}
