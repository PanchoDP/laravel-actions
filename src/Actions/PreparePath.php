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
    public static function handle(?string $folder_path, string $name, string $base_folder, bool $force = false): string
    {
        $parts = array_filter([$base_folder, $folder_path ?? '', "{$name}.php"], static fn (string $part): bool => $part !== '');
        $relativePath = implode('/', $parts);
        $path = app_path($relativePath);

        if (! $force && file_exists($path)) {
            throw new RuntimeException("Action {$name} already exists at app/{$relativePath}. Use --force to overwrite it.");
        }

        return $path;
    }
}
