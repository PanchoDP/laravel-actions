<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PrepareSubfolder
{
    /**
     * Prepare the subfolder string by replacing slashes with spaces and splitting it into an array.
     *
     * @param  string  $subfolder  The subfolder string to process.
     * @return array<string> An array of subfolder names.
     */
    public static function handle(string $subfolder): array
    {
        if (empty($subfolder)) {
            return [];
        }

        $subfolder = str_replace(['/', '\\'], ' ', $subfolder);

        return preg_split('/\s+/', $subfolder, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    }
}
