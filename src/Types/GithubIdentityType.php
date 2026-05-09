<?php

namespace Chuoke\UserIdentities\Types;

use Chuoke\UserIdentities\Types\Abstracts\OAuthBasedIdentityType;

class GithubIdentityType extends OAuthBasedIdentityType
{
    public function getType(): string
    {
        return 'github';
    }

    protected function getIdentifierRules(): array
    {
        return ['required', 'string'];
    }
}
