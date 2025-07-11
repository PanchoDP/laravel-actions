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
    public static function handle(?string $folder_path, string $name, string $base_folder): string
    {
        $path = app_path("{$base_folder}/{$folder_path}/{$name}.php");

        if (file_exists($path)) {
            throw new RuntimeException("Action {$name} already exists in the subfolder {$folder_path}.");
        }

        return $path;
    }
}
