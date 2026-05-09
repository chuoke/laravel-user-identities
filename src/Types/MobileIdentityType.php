<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\PasswordBasedIdentityType;

class MobileIdentityType extends PasswordBasedIdentityType
{
    public function getType(): string
    {
        return 'mobile';
    }

    protected function getIdentifierRules(): array
    {
        return ['required'];
    }
}
