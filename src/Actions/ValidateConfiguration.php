<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;

final class ValidateConfiguration
{
    public const string DEFAULT_BASE_FOLDER = 'Actions';

    public const string DEFAULT_METHOD_NAME = 'handle';

    /**
     * Read and validate the configured base folder.
     *
     * @throws InvalidArgumentException
     */
    public static function configuredBaseFolder(): string
    {
        return self::validateBaseFolder(self::configValue('base_folder'));
    }

    /**
     * Read and validate the configured method name.
     *
     * @throws InvalidArgumentException
     */
    public static function configuredMethodName(): string
    {
        return self::validateMethodName(self::configValue('method_name'));
    }

    /**
     * Validate and sanitize configuration values.
     *
     * @return array{base_folder: string, method_name: string}
     *
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
     * @throws InvalidArgumentException
     */
    private static function validateBaseFolder(?string $baseFolder): string
    {
        if ($baseFolder === null || $baseFolder === '') {
            return self::DEFAULT_BASE_FOLDER;
        }

        if (! preg_match('/^[a-zA-Z]\w*$/', $baseFolder)) {
            throw new InvalidArgumentException('Invalid base folder: must start with a letter and contain only letters, numbers, and underscores.');
        }

        return $baseFolder;
    }

    /**
     * Validate method name configuration.
     *
     * @throws InvalidArgumentException
     */
    private static function validateMethodName(?string $methodName): string
    {
        if ($methodName === null || $methodName === '') {
            return self::DEFAULT_METHOD_NAME;
        }

        if (! preg_match('/^[a-zA-Z_]\w*$/', $methodName)) {
            throw new InvalidArgumentException('Invalid method name: must be a valid PHP method name.');
        }

        // Names starting with "__" are reserved by PHP for magic methods
        // (__construct, __invoke, __get...), which would change how the class behaves.
        if (str_starts_with($methodName, '__')) {
            throw new InvalidArgumentException("Method name '{$methodName}' is not allowed: names starting with '__' are reserved for PHP magic methods.");
        }

        return $methodName;
    }

    private static function configValue(string $key): ?string
    {
        $value = config("laravel-actions.{$key}");

        return is_string($value) ? $value : null;
    }
}
