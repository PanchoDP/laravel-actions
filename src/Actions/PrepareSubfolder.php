<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PrepareSubfolder
{
    public static function handle(string $subfolder): string
    {
        if (empty($subfolder)) {
            return '';
        }

        return str_replace(['/', '\\'], '', $subfolder);

    }
}
