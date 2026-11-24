<?php

namespace Jimbojsb\Gatekeeper;

use BackedEnum;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Jimbojsb\Gatekeeper\Contracts\PermissionRegistry;

class GatekeeperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gatekeeper.php', 'gatekeeper');

        $this->app->singleton(PermissionRegistry::class, fn ($app) => $app->make(config('gatekeeper.registry')));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/gatekeeper.php' => config_path('gatekeeper.php'),
        ], ['gatekeeper', 'gatekeeper-config']);

        $this->publishes([
            __DIR__.'/../stubs/Role.php.stub' => app_path('Authorization/Role.php'),
            __DIR__.'/../stubs/Permission.php.stub' => app_path('Authorization/Permission.php'),
            __DIR__.'/../stubs/Registry.php.stub' => app_path('Authorization/Registry.php'),
        ], ['gatekeeper', 'gatekeeper-stubs']);

        if ($this->app->runningInConsole() && ! class_exists((string) config('gatekeeper.registry'))) {
            return;
        }

        $this->validateConfig();

        foreach (config('gatekeeper.permission_enum')::cases() as $permission) {
            Gate::define(
                (string) $permission->value,
                fn ($user) => $user->hasPermission($permission),
            );
        }

        Gate::before(fn ($user) => config('gatekeeper.super_admin_enabled')
            && method_exists($user, 'hasRole')
            && $user->hasRole(config('gatekeeper.super_admin_role')) ? true : null);
    }

    protected function validateConfig(): void
    {
        foreach (['role_enum', 'permission_enum'] as $key) {
            if (! is_subclass_of((string) config("gatekeeper.$key"), BackedEnum::class)) {
                throw new InvalidArgumentException("Gatekeeper: gatekeeper.$key must be a backed enum class.");
            }
        }

        if (! is_subclass_of((string) config('gatekeeper.registry'), PermissionRegistry::class)) {
            throw new InvalidArgumentException('Gatekeeper: gatekeeper.registry must implement '.PermissionRegistry::class.'.');
        }

        if (! is_string(config('gatekeeper.column')) || config('gatekeeper.column') === '') {
            throw new InvalidArgumentException('Gatekeeper: gatekeeper.column must be a non-empty string.');
        }

        $superAdmin = config('gatekeeper.super_admin_role');

        if (config('gatekeeper.super_admin_enabled') && (! is_string($superAdmin) || ! config('gatekeeper.role_enum')::tryFrom($superAdmin))) {
            throw new InvalidArgumentException('Gatekeeper: gatekeeper.super_admin_role must be a case value of the role enum.');
        }
    }
}
