<?php

use App\Authorization\Permission;
use App\Authorization\Registry;
use App\Authorization\Role;
use Jimbojsb\Gatekeeper\Gatekeeper;

it('publishes stubs that satisfy the default config', function () {
    foreach (['Role', 'Permission', 'Registry'] as $stub) {
        require_once __DIR__."/../../stubs/{$stub}.php.stub";
    }

    config()->set('gatekeeper.role_enum', Role::class);
    config()->set('gatekeeper.permission_enum', Permission::class);
    config()->set('gatekeeper.registry', Registry::class);

    Gatekeeper::validateConfig();

    expect((new Registry)->grantedBy(Role::Admin))->toBe(Permission::cases())
        ->and(require __DIR__.'/../../config/gatekeeper.php')->toMatchArray([
            'role_enum' => Role::class,
            'permission_enum' => Permission::class,
            'registry' => Registry::class,
        ]);
});
