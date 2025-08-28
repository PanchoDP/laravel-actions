<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;

final class PrepareSubfolder
{
    /**
     * Prepare the subfolder string by replacing slashes with spaces and splitting it into an array.
     *
     * @param  string  $subfolder  The subfolder string to process.
     * @return array<string> An array of subfolder names.
     *
     * @throws InvalidArgumentException
     */
    public static function handle(string $subfolder): array
    {
        if (empty($subfolder)) {
            return [];
        }

        // Security: Prevent path traversal attacks
        if (self::containsPathTraversal($subfolder)) {
            throw new InvalidArgumentException('Invalid subfolder path: path traversal sequences are not allowed.');
        }

        $subfolder = str_replace(['/', '\\'], ' ', $subfolder);

        return preg_split('/\s+/', $subfolder, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    }

    /**
     * Check if the path contains path traversal sequences.
     */
    private static function containsPathTraversal(string $path): bool
    {
        // Normalize the path to detect various path traversal attempts
        $normalizedPath = str_replace('\\', '/', $path);

        // Check for common path traversal patterns
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

        // Check for absolute paths
        if (preg_match('/^([a-z]:|\/)/i', $normalizedPath)) {
            return true;
        }

        return false;
    }
}
