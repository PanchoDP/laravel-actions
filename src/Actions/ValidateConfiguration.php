<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;

final class ValidateConfiguration
{
    /**
     * Validate and sanitize configuration values.
     *
     * @param string|null $baseFolder
     * @param string|null $methodName
     * @return array{base_folder: string, method_name: string}
     * @throws InvalidArgumentException
     */
    public static function handle(?string $baseFolder, ?string $methodName): array
    {
        $validatedBaseFolder = self::validateBaseFolder($baseFolder);
        $validatedMethodName = self::validateMethodName($methodName);

        return [
            'base_folder' => $validatedBaseFolder,
            'method_name' => $validatedMethodName,
        ];
    }

    /**
     * Validate base folder configuration.
     *
     * @param string|null $baseFolder
     * @return string
     * @throws InvalidArgumentException
     */
    private static function validateBaseFolder(?string $baseFolder): string
    {
        if ($baseFolder === null || $baseFolder === '') {
            return 'Actions';
        }

        // Security: Prevent path traversal in base folder
        if (self::containsPathTraversal($baseFolder)) {
            throw new InvalidArgumentException('Invalid base folder: path traversal sequences are not allowed.');
        }

        // Validate folder name format
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $baseFolder)) {
            throw new InvalidArgumentException('Invalid base folder: must start with a letter and contain only letters, numbers, and underscores.');
        }

        return $baseFolder;
    }

    /**
     * Validate method name configuration.
     *
     * @param string|null $methodName
     * @return string
     * @throws InvalidArgumentException
     */
    private static function validateMethodName(?string $methodName): string
    {
        if ($methodName === null || $methodName === '') {
            return 'handle';
        }

        // Validate method name format (valid PHP method name)
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $methodName)) {
            throw new InvalidArgumentException('Invalid method name: must be a valid PHP method name.');
        }

        // Security: Prevent dangerous method names
        $dangerousMethods = ['__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', 'eval', 'exec', 'system'];
        if (in_array(strtolower($methodName), $dangerousMethods, true)) {
            throw new InvalidArgumentException("Method name '{$methodName}' is not allowed for security reasons.");
        }

        return $methodName;
    }

    /**
     * Check if the path contains path traversal sequences.
     *
     * @param string $path
     * @return bool
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
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (stripos($normalizedPath, $pattern) !== false) {
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