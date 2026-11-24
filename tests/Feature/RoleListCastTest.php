<?php

use Jimbojsb\Gatekeeper\Tests\Fixtures\Role;
use Jimbojsb\Gatekeeper\Tests\Fixtures\User;

it('reads enums and drops unknown strings', function () {
    $user = User::create();
    User::query()->whereKey($user->id)->update(['roles' => json_encode(['editor', 'ghost'])]);

    expect($user->fresh()->roles->all())->toBe([Role::Editor]);
});

it('writes roles deduped and sorted', function () {
    $user = User::create(['roles' => [Role::Writer, 'admin', Role::Writer]]);

    expect($user->getAttributes()['roles'])->toBe('["admin","writer"]');
});

it('rejects unknown roles on write', function () {
    User::create(['roles' => ['ghost']]);
})->throws(InvalidArgumentException::class);

it('treats null and empty as no roles', function () {
    expect(User::create(['roles' => null])->fresh()->roles)->toBeEmpty()
        ->and(User::create(['roles' => []])->fresh()->roles)->toBeEmpty()
        ->and((new User)->roles())->toBeEmpty();
});
