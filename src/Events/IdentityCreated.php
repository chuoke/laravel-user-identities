<?php

namespace Chuoke\UserIdentities\Events;

use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Foundation\Events\Dispatchable;

class IdentityCreated
{
    use Dispatchable;

    public function __construct(
        public UserIdentity $identity
    ) {
    }
}
