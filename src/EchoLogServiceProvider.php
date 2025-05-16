<?php

namespace Ecomac\EchoLog;

use Illuminate\Support\ServiceProvider;
use Ecomac\EchoLog\Console\MonitorLogError;

class EchoLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/echo-log.php', 'echo-log');

    }
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/echo-log.php' => $this->app->basePath('config/echo-log.php'),
        ], 'echo-log-config');

        // Registra el comando si se ejecuta en la consola
        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorLogError::class,
            ]);
        }
    }
}
