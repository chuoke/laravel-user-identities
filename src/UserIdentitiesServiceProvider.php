<?php

namespace Chuoke\UserIdentities;

use Chuoke\UserIdentities\Auth\IdentityGuard;
use Chuoke\UserIdentities\Auth\IdentityUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class UserIdentitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/user-identities.php',
            'user-identities'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/user-identities.php' => config_path('user-identities.php'),
        ], 'user-identities-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'user-identities-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register custom user provider
        Auth::provider('user-identity', function ($app, array $config) {
            return new IdentityUserProvider($app['hash'], $config['model']);
        });

        // Register custom guard
        Auth::extend('user-identity', function ($app, $name, array $config) {
            return new IdentityGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request']
            );
        });
    }
}
