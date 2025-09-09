<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction;

use Illuminate\Support\ServiceProvider;
use Panchodp\LaravelAction\Console\MakeActionCommand;
use Panchodp\LaravelAction\Console\MakeActionListCommand;

final class LaravelActionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeActionCommand::class,
                MakeActionListCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/laravel-actions.php' => config_path('laravel-actions.php'),
        ], 'laravel-actions-config');

    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {

        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-actions.php',
            'laravel-actions'
        );
    }
}
