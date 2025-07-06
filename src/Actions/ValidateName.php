<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;
use Throwable;

final class ValidateName
{
    /**
     * Handle the action.
     *
     * @throws Throwable
     */
    public static function handle(?string $name): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Please input the name of the action class.');
        }

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('The name provided for the class name is invalid. It uses only letters, numbers, and underscores, and cannot begin with a number.');
        }
    }
}
