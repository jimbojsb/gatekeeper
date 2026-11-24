<?php

namespace Jimbojsb\Gatekeeper\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RoleList implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $enum = config('gatekeeper.role_enum');

        return collect(json_decode($value ?? '[]', true) ?: [])
            ->map(fn ($role) => is_string($role) || is_int($role) ? $enum::tryFrom($role) : null)
            ->filter()
            ->unique(fn (BackedEnum $role) => $role->value)
            ->values();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $enum = config('gatekeeper.role_enum');

        $roles = collect($value instanceof BackedEnum ? [$value] : $value)
            ->map(fn ($role) => ($role instanceof $enum ? $role : $enum::from($role))->value)
            ->unique()
            ->sort()
            ->values();

        return [$key => $roles->toJson()];
    }
}
