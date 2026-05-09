<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\PasswordBasedIdentityType;

class EmailIdentityType extends PasswordBasedIdentityType
{
    public function getType(): string
    {
        return 'email';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'email'];
    }
}
