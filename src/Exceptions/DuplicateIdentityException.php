<?php

namespace Chuoke\UserIdentities\Exceptions;

use Exception;

class DuplicateIdentityException extends Exception
{
    public static function make(string $type, string $identifier): self
    {
        return new self("Identity with type '{$type}' and identifier '{$identifier}' already exists");
    }
}
