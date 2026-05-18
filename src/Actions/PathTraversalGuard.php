<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PathTraversalGuard
{
    public static function check(string $path): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_contains($normalizedPath, '../')) {
            return true;
        }

        return (bool) preg_match('/^([a-z]:|\/)/i', $normalizedPath);
    }
}
