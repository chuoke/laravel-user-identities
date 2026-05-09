<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\OAuthBasedIdentityType;

class TwitterIdentityType extends OAuthBasedIdentityType
{
    public function getType(): string
    {
        return 'twitter';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string', 'regex:/^[a-zA-Z0-9_]{1,15}$/'];
    }
}
