<?php

namespace Jimbojsb\Gatekeeper\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Jimbojsb\Gatekeeper\GatekeeperServiceProvider;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Permission;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Registry;
use Jimbojsb\Gatekeeper\Tests\Fixtures\Role;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [GatekeeperServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('gatekeeper.role_enum', Role::class);
        $app['config']->set('gatekeeper.permission_enum', Permission::class);
        $app['config']->set('gatekeeper.registry', Registry::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->json('roles')->nullable();
        });
    }
}
