<?php

namespace Jimbojsb\Gatekeeper\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Jimbojsb\Gatekeeper\Casts\RoleList;
use Jimbojsb\Gatekeeper\Contracts\PermissionRegistry;

trait HasRoles
{
    public function initializeHasRoles(): void
    {
        $this->mergeCasts([config('gatekeeper.column') => RoleList::class]);
    }

    public function roles(): Collection
    {
        return $this->getAttribute(config('gatekeeper.column')) ?? collect();
    }

    public function hasRole(BackedEnum|string $role): bool
    {
        $role = $role instanceof BackedEnum ? $role : config('gatekeeper.role_enum')::from($role);

        return $this->roles()->contains(fn (BackedEnum $held) => $held === $role);
    }

    public function permissions(): Collection
    {
        $registry = app(PermissionRegistry::class);

        return $this->roles()
            ->flatMap(fn (BackedEnum $role) => $registry->grantedBy($role))
            ->unique(fn (BackedEnum $permission) => $permission->value)
            ->values();
    }

    public function hasPermission(BackedEnum|string $permission): bool
    {
        $permission = $permission instanceof BackedEnum ? $permission : config('gatekeeper.permission_enum')::from($permission);

        return $this->permissions()->contains(fn (BackedEnum $held) => $held === $permission);
    }

    public function scopeWhereRole(Builder $query, BackedEnum|string $role): Builder
    {
        $role = $role instanceof BackedEnum ? $role : config('gatekeeper.role_enum')::from($role);

        return $query->whereJsonContains($this->qualifyColumn(config('gatekeeper.column')), $role->value);
    }
}
