<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PrepareSubfolder
{
    public static function handle(string $subfolder): ?array
    {
        if (empty($subfolder)) {
            return [];
        }

        $subfolder = str_replace(['/', '\\'], ' ', $subfolder);

        return preg_split('/\s+/', $subfolder, -1, PREG_SPLIT_NO_EMPTY);

    }
}
