<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction;

use Illuminate\Support\ServiceProvider;
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
            ]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void {}
}
