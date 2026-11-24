<?php

namespace Jimbojsb\Gatekeeper\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Jimbojsb\Gatekeeper\Gatekeeper;

class RoleList implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            return collect();
        }

        $enum = Gatekeeper::roleEnum();

        return collect($decoded)
            ->filter(fn ($role) => is_string($role) || is_int($role))
            ->map(fn ($role) => $enum::tryFrom($role))
            ->filter()
            ->unique(fn (BackedEnum $role) => $role->value)
            ->values();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => json_encode([])];
        }

        if ($value instanceof BackedEnum || is_string($value)) {
            $value = [$value];
        }

        if (! is_iterable($value)) {
            throw new InvalidArgumentException('Gatekeeper: roles must be an iterable of roles.');
        }

        $roles = collect($value)
            ->map(fn ($role) => Gatekeeper::role($role)->value)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [$key => json_encode($roles)];
    }
}
