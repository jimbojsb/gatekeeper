<?php

namespace Jimbojsb\Gatekeeper\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Jimbojsb\Gatekeeper\Casts\RoleList;
use Jimbojsb\Gatekeeper\Gatekeeper;

trait HasRoles
{
    private ?string $gatekeeperCacheKey = null;

    private ?Collection $gatekeeperPermissions = null;

    public function initializeHasRoles(): void
    {
        $this->mergeCasts([Gatekeeper::column() => RoleList::class]);
    }

    public function roles(): Collection
    {
        return $this->getAttribute(Gatekeeper::column()) ?? collect();
    }

    public function hasRole(BackedEnum|string $role): bool
    {
        $role = Gatekeeper::role($role);

        return $this->roles()->contains(fn (BackedEnum $held) => $held === $role);
    }

    public function permissions(): Collection
    {
        $key = (string) ($this->getAttributes()[Gatekeeper::column()] ?? '');

        if ($this->gatekeeperPermissions !== null && $this->gatekeeperCacheKey === $key) {
            return $this->gatekeeperPermissions;
        }

        $registry = Gatekeeper::registry();

        $this->gatekeeperCacheKey = $key;

        return $this->gatekeeperPermissions = $this->roles()
            ->flatMap(fn (BackedEnum $role) => $registry->grantedBy($role))
            ->unique(fn (BackedEnum $permission) => $permission->value)
            ->values();
    }

    public function hasPermission(BackedEnum|string $permission): bool
    {
        $permission = Gatekeeper::permission($permission);

        return $this->permissions()->contains(fn (BackedEnum $held) => $held === $permission);
    }

    public function scopeWhereRole(Builder $query, BackedEnum|string $role): Builder
    {
        return $query->whereJsonContains(
            $this->qualifyColumn(Gatekeeper::column()),
            Gatekeeper::role($role)->value,
        );
    }
}
