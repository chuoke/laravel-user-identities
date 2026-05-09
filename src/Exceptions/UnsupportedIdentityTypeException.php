<?php

namespace Chuoke\UserIdentities\Exceptions;

use Exception;

class UnsupportedIdentityTypeException extends Exception
{
    public static function make(string $type): self
    {
        return new self("Unsupported identity type: {$type}");
    }
}
