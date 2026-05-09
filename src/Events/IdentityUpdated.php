<?php

namespace Chuoke\UserIdentities\Events;

use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Foundation\Events\Dispatchable;

class IdentityUpdated
{
    use Dispatchable;

    public function __construct(
        public UserIdentity $identity,
        public array $changes
    ) {
    }
}
