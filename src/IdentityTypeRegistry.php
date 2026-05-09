<?php

namespace Chuoke\UserIdentities;

use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use Chuoke\UserIdentities\Exceptions\UnsupportedIdentityTypeException;

class IdentityTypeRegistry
{
    protected static array $types = [];

    protected static bool $loaded = false;

    public static function register(IdentityTypeInterface $type): void
    {
        static::ensureLoaded();
        static::$types[$type->getType()] = $type;
    }

    public static function get(string $type): ?IdentityTypeInterface
    {
        static::ensureLoaded();

        if (! isset(static::$types[$type])) {
            throw UnsupportedIdentityTypeException::make($type);
        }

        return static::$types[$type];
    }

    public static function all(): array
    {
        static::ensureLoaded();

        return static::$types;
    }

    public static function forget(string $type): void
    {
        static::ensureLoaded();
        unset(static::$types[$type]);
    }

    public static function has(string $type): bool
    {
        static::ensureLoaded();

        return isset(static::$types[$type]);
    }

    protected static function ensureLoaded(): void
    {
        if (! static::$loaded) {
            static::$loaded = true;
            static::loadDefaults();
        }
    }

    protected static function loadDefaults(): void
    {
        $types = config('user-identities.types');

        foreach ($types as $type => $class) {
            static::register(new $class());
        }
    }
}
