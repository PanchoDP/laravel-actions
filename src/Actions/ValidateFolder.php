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
     * @throws Throwable
     */
    public static function handle(?string $subfolder): void
    {
        if (! empty($subfolder)) {
            if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $subfolder)) {
                throw new InvalidArgumentException("Invalid folder name: {$subfolder}. 
        Folder names must start with a letter or underscore and can only contain letters, numbers, and underscores.");
            }
        }

    }
}
