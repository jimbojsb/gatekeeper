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

];
