<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use Illuminate\Support\Facades\File;
use RuntimeException;

final class CreateDirectory
{
    /**
     * Ensure the directory holding the given file path exists.
     *
     * @return bool True when the directory had to be created, false when it already existed.
     *
     * @throws RuntimeException
     */
    public static function handle(string $path): bool
    {
        $directory = dirname($path);

        if (is_dir($directory)) {
            return false;
        }

        File::ensureDirectoryExists($directory, 0755, true);

        if (! is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: {$directory}. Check the parent directory permissions.");
        }

        return true;
    }
}
