<?php

namespace Ecomac\EchoLog;

use Ecomac\EchoLog\Contracts\ClockProvider;
use Ecomac\EchoLog\Services\CarbonService;
use Illuminate\Support\ServiceProvider;
use Ecomac\EchoLog\Console\MonitorLogError;

/**
 * Class EchoLogServiceProvider
 *
 * Service provider for the EchoLog package.
 *
 * Responsibilities include:
 * - Registering package configurations.
 * - Binding interfaces to concrete implementations.
 * - Publishing configuration files.
 * - Loading the package's views.
 * - Registering Artisan commands when running in console.
 *
 * This class can be extended to include additional services, commands, or resources as needed.
 */
class EchoLogServiceProvider extends ServiceProvider
{
    /**
     * Register services and bindings in Laravel's service container.
     *
     * This merges the package configuration with the application configuration,
     * and binds the ClockProvider interface to the CarbonService implementation.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/echo-log.php', 'echo-log');
        $this->mergeConfigFrom(__DIR__ . '/../config/error-categories.php', 'error-categories');
        $this->app->bind(ClockProvider::class, CarbonService::class);
    }

    /**
     * Bootstrap services after registration.
     *
     * Publishes configuration files for overriding,
     * loads package views,
     * and registers Artisan commands if the app is running in the console.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/echo-log.php' => $this->app->basePath('config/echo-log.php'),
        ], 'echo-log-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'echo-log');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorLogError::class,
            ]);
        }
    }
}
