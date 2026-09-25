<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction;

use Illuminate\Support\ServiceProvider;
use Override;
use Panchodp\LaravelAction\Console\ListActionsCommand;
use Panchodp\LaravelAction\Console\MakeActionCommand;

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
                ListActionsCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/laravel-actions.php' => config_path('laravel-actions.php'),
        ], 'laravel-actions-config');

        $this->publishes([
            __DIR__.'/../stubs' => resource_path('stubs/vendor/laravel-actions'),
        ], 'laravel-actions-stubs');
    }

    /**
     * Register the service provider.
     */
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-actions.php',
            'laravel-actions'
        );
    }
}
