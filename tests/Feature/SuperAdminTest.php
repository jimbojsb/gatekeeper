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

it('ignores the super admin role while disabled', function () {
    config()->set('gatekeeper.super_admin_enabled', false);
    config()->set('gatekeeper.super_admin_role', 'root');

    (new GatekeeperServiceProvider(app()))->boot();
})->throwsNoExceptions();

it('rejects a super admin role missing from the role enum', function () {
    config()->set('gatekeeper.super_admin_role', 'root');

    (new GatekeeperServiceProvider(app()))->boot();
})->throws(InvalidArgumentException::class);
