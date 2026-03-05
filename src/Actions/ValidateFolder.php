<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;
use Throwable;

final class ValidateFolder
{
    /**
     * Handle the action.
     *
     * @param  array<string>  $folders
     *
     * @throws Throwable
     */
    public static function handle(array $folders): void
    {
        foreach ($folders as $folder) {
            if (! empty($folder) && ! preg_match('/^[A-Za-z_]\w*$/', $folder)) {
                throw new InvalidArgumentException("Invalid folder name: {$folder}. 
        Folder names must start with a letter or underscore and can only contain letters, numbers, and underscores.");
            }

        }

    }
}
