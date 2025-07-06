<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;

final class PreparePath
{
    /**
     * Handle the action.
     *
     * @throws RuntimeException
     */
    public static function handle(string $subfolder, string $name): string
    {
        $path = app_path("Actions/{$subfolder}/{$name}.php");

        if (file_exists($path)) {
            throw new RuntimeException("Action {$name} already exists in the subfolder {$subfolder}.");
        }

        return $path;
    }
}
