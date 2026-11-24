<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role Enum
    |--------------------------------------------------------------------------
    |
    | The backed enum listing every role in your application. The default
    | points at the stub published with --tag=gatekeeper-stubs. Its case
    | values are what get stored in the user's roles column, so treat
    | renaming a value like a data migration.
    |
    */

    'role_enum' => App\Authorization\Role::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Enum
    |--------------------------------------------------------------------------
    |
    | The backed enum listing every permission. The default points at the
    | published stub. One Gate ability is registered per case, named after
    | the case value, so @can('manage-posts') works out of the box.
    |
    */

    'permission_enum' => App\Authorization\Permission::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Registry
    |--------------------------------------------------------------------------
    |
    | A class implementing Jimbojsb\Gatekeeper\Contracts\PermissionRegistry.
    | Its grantedBy() method maps each role to the permissions it grants,
    | typically with a match expression. A role can include another role's
    | grants by spreading them: [...$this->grantedBy(Role::Editor), ...].
    | The default points at the published stub.
    |
    */

    'registry' => App\Authorization\Registry::class,

    /*
    |--------------------------------------------------------------------------
    | Roles Column
    |--------------------------------------------------------------------------
    |
    | The JSON column on your users table that holds the user's role values.
    | Add it yourself with a migration; this package ships none.
    |
    */

    'column' => 'roles',

    /*
    |--------------------------------------------------------------------------
    | Super Administrator Role
    |--------------------------------------------------------------------------
    |
    | Users holding this role pass every Gate check: a Gate::before hook
    | allows every ability, including your own Gate definitions and
    | policies. This is a Gate rule, not a permission, so hasPermission()
    | still reports only what the registry maps the role to.
    |
    | The role is a case value of your role enum. The published Role stub
    | includes a SuperAdmin case for it. If your enum has no such case, the
    | bypass does nothing.
    |
    | Set super_admin_enabled to false to switch the bypass off without
    | touching stored roles. Users keep the role and get only what the
    | registry maps it to; switch it back on and the bypass returns.
    |
    */

    'super_admin_role' => 'super-admin',

    'super_admin_enabled' => env('GATEKEEPER_SUPER_ADMIN', true),

];
