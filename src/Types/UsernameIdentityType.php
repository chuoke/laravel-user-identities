<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\PasswordBasedIdentityType;

class UsernameIdentityType extends PasswordBasedIdentityType
{
    public function getType(): string
    {
        return 'username';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string'];
    }
}
