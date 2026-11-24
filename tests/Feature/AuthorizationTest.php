<?php

use Illuminate\Support\Facades\Gate;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Role;
use Jimbojsb\Gatekeeper\Tests\Fixtures\User;

it('defines a gate ability per permission', function () {
    $user = User::create(['roles' => [Role::Editor]]);

    expect(Gate::forUser($user)->allows('manage-posts'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('manage-users'))->toBeFalse();
});

it('denies guests', function () {
    expect(Gate::allows('manage-posts'))->toBeFalse();
});
