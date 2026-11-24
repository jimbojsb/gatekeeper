<?php

use Illuminate\Support\Facades\Gate;
use Jimbojsb\Gatekeeper\GatekeeperServiceProvider;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Role;
use Jimbojsb\Gatekeeper\Tests\Fixtures\User;

it('lets a super admin past every gate check by default', function () {
    Gate::define('launch-rockets', fn ($user) => false);

    $superAdmin = User::create(['roles' => [Role::SuperAdmin]]);
    $admin = User::create(['roles' => [Role::Admin]]);

    expect(Gate::forUser($superAdmin)->allows('launch-rockets'))->toBeTrue()
        ->and(Gate::forUser($superAdmin)->allows('manage-users'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('launch-rockets'))->toBeFalse()
        ->and(Gate::allows('launch-rockets'))->toBeFalse();
});

it('does not grant a super admin any permissions', function () {
    expect(User::create(['roles' => [Role::SuperAdmin]])->permissions())->toBeEmpty();
});

it('can be turned off', function () {
    config()->set('gatekeeper.super_admin_enabled', false);
    Gate::define('launch-rockets', fn ($user) => false);

    expect(Gate::forUser(User::create(['roles' => [Role::SuperAdmin]]))->allows('launch-rockets'))->toBeFalse();
});

it('does nothing when the super admin role is not in the role enum', function () {
    config()->set('gatekeeper.super_admin_role', 'root');
    Gate::define('launch-rockets', fn ($user) => false);

    $editor = User::create(['roles' => [Role::Editor]]);

    expect(Gate::forUser($editor)->allows('launch-rockets'))->toBeFalse()
        ->and(Gate::forUser($editor)->allows('manage-posts'))->toBeTrue();
});

it('boots without the app enums published', function () {
    config()->set('gatekeeper.role_enum', 'App\Authorization\Missing');
    config()->set('gatekeeper.permission_enum', 'App\Authorization\Missing');
    config()->set('gatekeeper.registry', 'App\Authorization\Missing');

    (new GatekeeperServiceProvider(app()))->boot();
})->throwsNoExceptions();
