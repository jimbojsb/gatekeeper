<?php

namespace Jimbojsb\Gatekeeper\Tests\Fixtures;

use BackedEnum;
use Jimbojsb\Gatekeeper\Contracts\PermissionRegistry;

class Registry implements PermissionRegistry
{
    public function grantedBy(BackedEnum $role): array
    {
        return match ($role) {
            Role::Writer => [Permission::WriteDrafts],
            Role::Editor => [...$this->grantedBy(Role::Writer), Permission::ManagePosts],
            Role::Admin => [...$this->grantedBy(Role::Editor), Permission::ManageUsers],
        };
    }
}
