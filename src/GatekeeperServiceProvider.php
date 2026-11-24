<?php

namespace Jimbojsb\Gatekeeper;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
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

        if (enum_exists((string) config('gatekeeper.permission_enum'))) {
            foreach (config('gatekeeper.permission_enum')::cases() as $permission) {
                Gate::define(
                    (string) $permission->value,
                    fn ($user) => $user->hasPermission($permission),
                );
            }
        }

        Gate::before(function ($user) {
            if (! config('gatekeeper.super_admin_enabled') || ! method_exists($user, 'hasRole')) {
                return null;
            }

            $role = config('gatekeeper.role_enum')::tryFrom((string) config('gatekeeper.super_admin_role'));

            return $role && $user->hasRole($role) ? true : null;
        });
    }
}
