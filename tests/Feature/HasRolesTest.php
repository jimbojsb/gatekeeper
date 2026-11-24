<?php

use Jimbojsb\Gatekeeper\Tests\Fixtures\Permission;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Role;
use Jimbojsb\Gatekeeper\Tests\Fixtures\User;

it('checks roles and unions permissions', function () {
    $user = User::create(['roles' => [Role::Editor, Role::Writer]]);

    expect($user->hasRole('editor'))->toBeTrue()
        ->and($user->hasRole(Role::Admin))->toBeFalse()
        ->and($user->permissions()->all())->toBe([Permission::WriteDrafts, Permission::ManagePosts])
        ->and($user->hasPermission(Permission::ManagePosts))->toBeTrue()
        ->and($user->hasPermission('manage-users'))->toBeFalse();
});

it('picks up role changes mid-request', function () {
    $user = User::create(['roles' => [Role::Writer]]);
    expect($user->hasPermission(Permission::ManageUsers))->toBeFalse();

    $user->roles = [Role::Admin];

    expect($user->hasPermission(Permission::ManageUsers))->toBeTrue();
});

it('scopes queries by role', function () {
    User::create(['roles' => [Role::Admin]]);
    User::create(['roles' => [Role::Writer]]);

    expect(User::whereRole(Role::Admin)->count())->toBe(1);
});
