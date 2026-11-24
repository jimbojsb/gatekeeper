# Gatekeeper

Roles and permissions for Laravel, defined in code. Roles, permissions and the role-to-permission map are PHP enums and a class. The only stored data is a JSON array of role values on your users table.

Requires PHP 8.3+ and Laravel 12 or 13.

## Installation

```bash
composer require jimbojsb/gatekeeper
php artisan vendor:publish --tag=gatekeeper
```

This publishes `config/gatekeeper.php` and three stubs into `app/Authorization`. Use `--tag=gatekeeper-config` or `--tag=gatekeeper-stubs` to publish only one set.

Add a JSON column to your users table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('roles')->nullable();
});
```

## Setup

Keep the role enum, permission enum and registry together in `App\Authorization`. The published stubs put them there, and the default config points at them:

```
app/Authorization/
├── Permission.php
├── Registry.php
└── Role.php
```

Each stub starts with one example case and comments on how to fill it in. The examples below show them filled out.

Policies stay in `app/Policies` so Laravel's policy auto-discovery finds them.

### 1. Define roles and permissions

Both are plain backed enums.

```php
namespace App\Authorization;

enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case Writer = 'writer';
}
```

```php
namespace App\Authorization;

enum Permission: string
{
    case ManagePosts = 'manage-posts';
    case ManageUsers = 'manage-users';
    case WriteDrafts = 'write-drafts';
}
```

### 2. Map roles to permissions

Implement `PermissionRegistry`. A role can include another role's grants with the spread operator.

```php
namespace App\Authorization;

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
            default => [],
        };
    }
}
```

Roles without an arm, including the super administrator, fall through to `default` and grant nothing.

### 3. Configure

If you follow the `App\Authorization` convention, the defaults need no changes:

```php
return [
    'role_enum' => App\Authorization\Role::class,
    'permission_enum' => App\Authorization\Permission::class,
    'registry' => App\Authorization\Registry::class,
    'column' => 'roles',
    'super_admin_role' => 'super-admin',
    'super_admin_enabled' => env('GATEKEEPER_SUPER_ADMIN', true),
];
```

The config is validated on boot. An invalid enum or registry class, or a `super_admin_role` that isn't a case of the role enum, throws. Console commands skip the check until the registry class exists, so `composer require` and `vendor:publish` work before the stubs are published.

### 4. Add the trait to your user model

```php
use Jimbojsb\Gatekeeper\Concerns\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

The trait registers the cast on the roles column itself. Don't add it to `$casts`.

## Assigning roles

Assign enums, strings, or a mix. A single role is accepted too.

```php
$user->roles = [Role::Editor, 'writer'];
$user->save();

$user->update(['roles' => Role::Admin]);

$user->roles = [];
```

Writes are stored deduplicated and sorted. An unknown role throws a `ValueError`, as do `hasRole()`, `hasPermission()` and `whereRole()` given an unknown string.

## Reading roles

`$user->roles` is a collection of `Role` cases.

```php
$user->roles;
$user->hasRole(Role::Editor);
$user->hasRole('editor');
```

Unknown values in the database (for example, a role you removed from the enum) are dropped on read, so they grant nothing. `null` and `[]` both mean no roles.

## Checking permissions

```php
$user->hasPermission(Permission::ManagePosts);
$user->hasPermission('manage-posts');
$user->permissions();
```

`permissions()` returns the deduplicated union of every role's grants. It is computed on each call, so `$user->roles = [...]` takes effect immediately.

## Gate abilities

One Gate ability is registered per `Permission` case, named after the case value. Guests are always denied.

```php
Gate::allows('manage-posts');
$user->can('manage-posts');
```

```blade
@can('manage-users')
    ...
@endcan
```

```php
Route::get('/admin/users', ...)->middleware('can:manage-users');
```

## Super administrator

The bypass is on by default. Users holding the role named in `super_admin_role` (`super-admin` unless you change it) pass every Gate check: a `Gate::before` hook allows every ability, including your own `Gate::define` abilities and policy methods.

The bypass is a Gate rule, not a permission. The registry maps the role to no permissions, so `permissions()` is empty and `hasPermission()` is false for a super admin. Check authorization with `can()`, `@can`, the `can:` middleware or a policy wherever a super admin should get through.

The hook only ever allows. For everyone else it returns `null`, so normal checks run. User models without the `HasRoles` trait are ignored by it.

To turn the bypass off, set `GATEKEEPER_SUPER_ADMIN=false` or the config value:

```php
'super_admin_enabled' => false,
```

Stored roles are untouched. Users keep the role, which then behaves like any other, and turning the flag back on restores the bypass. If you don't want the role at all, also delete the `SuperAdmin` case from your role enum. `super_admin_role` is only checked while the bypass is enabled.

To use a different role as the super administrator, set `super_admin_role` to that role's value.

## Querying by role

```php
User::whereRole(Role::Admin)->get();
User::whereRole('editor')->count();
```

This uses `whereJsonContains`, so it works on MySQL, MariaDB, PostgreSQL and SQLite.

## Writing your own checks

Inside Gate closures and policies, call `$user->hasPermission()` directly. Don't call `$user->can()`, which goes back through the Gate.

## Octane and queue workers

The registry is resolved from the container each time permissions are computed, not captured at boot. It is bound as a singleton, so keep it stateless.

## Testing

```bash
composer install
vendor/bin/pest
```
