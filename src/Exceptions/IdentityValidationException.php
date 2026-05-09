<?php

namespace Chuoke\UserIdentities\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class IdentityValidationException extends Exception
{
    protected MessageBag $errors;

    public function __construct(MessageBag $errors, string $message = 'Identity validation failed')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): MessageBag
    {
        return $this->errors;
    }

    public static function make(MessageBag $errors, string $message = 'Identity validation failed'): self
    {
        return new self($errors, $message);
    }
}
