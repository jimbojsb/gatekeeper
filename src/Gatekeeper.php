<?php

namespace Jimbojsb\Gatekeeper;

use BackedEnum;
use InvalidArgumentException;
use Jimbojsb\Gatekeeper\Contracts\PermissionRegistry;

final class Gatekeeper
{
    public static function roleEnum(): string
    {
        return config('gatekeeper.role_enum');
    }

    public static function permissionEnum(): string
    {
        return config('gatekeeper.permission_enum');
    }

    public static function column(): string
    {
        return config('gatekeeper.column', 'roles');
    }

    public static function registry(): PermissionRegistry
    {
        return app(PermissionRegistry::class);
    }

    public static function role(BackedEnum|string $role): BackedEnum
    {
        return self::coerce(self::roleEnum(), $role, 'role');
    }

    public static function permission(BackedEnum|string $permission): BackedEnum
    {
        return self::coerce(self::permissionEnum(), $permission, 'permission');
    }

    public static function validateConfig(): void
    {
        foreach (['role_enum', 'permission_enum'] as $key) {
            $class = config("gatekeeper.$key");

            if (! is_string($class) || ! enum_exists($class) || ! is_subclass_of($class, BackedEnum::class)) {
                throw new InvalidArgumentException("Gatekeeper: config gatekeeper.$key must be a backed enum class.");
            }
        }

        $registry = config('gatekeeper.registry');

        if (! is_string($registry) || ! is_subclass_of($registry, PermissionRegistry::class)) {
            throw new InvalidArgumentException('Gatekeeper: config gatekeeper.registry must implement '.PermissionRegistry::class.'.');
        }

        $column = config('gatekeeper.column');

        if (! is_string($column) || $column === '') {
            throw new InvalidArgumentException('Gatekeeper: config gatekeeper.column must be a non-empty string.');
        }
    }

    private static function coerce(string $enum, BackedEnum|string $value, string $kind): BackedEnum
    {
        if ($value instanceof $enum) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            throw new InvalidArgumentException("Gatekeeper: expected $enum $kind, got ".$value::class.'.');
        }

        return $enum::tryFrom($value)
            ?? throw new InvalidArgumentException("Gatekeeper: unknown $kind [$value].");
    }
}
