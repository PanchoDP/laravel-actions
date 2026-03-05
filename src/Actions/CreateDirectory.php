<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class CreateDirectory
{
    public static function handle(string $path): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
