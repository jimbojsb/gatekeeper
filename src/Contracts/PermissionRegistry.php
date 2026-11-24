<?php

namespace Jimbojsb\Gatekeeper\Contracts;

use BackedEnum;

interface PermissionRegistry
{
    public function grantedBy(BackedEnum $role): array;
}
