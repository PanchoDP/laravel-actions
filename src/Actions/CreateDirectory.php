<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class CreateDirectory
{
    public static function handle(string $path, int $permissions = 0750): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, $permissions, true);
        }
    }
}
