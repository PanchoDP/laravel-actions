<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PathTraversalGuard
{
    public static function check(string $path): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);

        $dangerousPatterns = [
            '../',     // Standard path traversal
            '..\\',    // Windows path traversal
            '..%2f',   // URL encoded forward slash
            '..%5c',   // URL encoded backslash
            '..%252f', // Double URL encoded forward slash
            '..%255c', // Double URL encoded backslash
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (mb_stripos($normalizedPath, $pattern) !== false) {
                return true;
            }
        }

        return (bool) preg_match('/^([a-z]:|\/)/i', $normalizedPath);
    }
}
