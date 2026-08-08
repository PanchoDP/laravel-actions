<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class GenerateRequest
{
    /**
     * Build and validate the Request class name for a given action name.
     *
     * Call this before touching the filesystem so an invalid name never
     * leaves a half-created directory structure behind.
     *
     * @param  string  $actionName  The base name for the action (e.g., 'CreateUser')
     *
     * @throws InvalidArgumentException
     */
    public static function requestNameFor(string $actionName): string
    {
        if ($actionName === '') {
            throw new InvalidArgumentException('Action name cannot be empty.');
        }

        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $actionName)) {
            throw new InvalidArgumentException("Cannot generate a Request class for '{$actionName}'. Names used with --request must start with an uppercase letter and contain only letters and numbers.");
        }

        return $actionName.'Request';
    }

    /**
     * Generate a Laravel Request class using Artisan command
     *
     * @param  string  $actionName  The base name for the action (e.g., 'CreateUser')
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public static function handle(string $actionName, bool $force = false): string
    {
        $requestName = self::requestNameFor($actionName);

        $parameters = ['name' => $requestName];

        if ($force) {
            $parameters['--force'] = true;
        }

        try {
            $exitCode = Artisan::call('make:request', $parameters);
        } catch (Throwable $e) {
            throw new RuntimeException("Error generating Request class {$requestName}: ".$e->getMessage(), 0, $e);
        }

        if ($exitCode !== 0) {
            throw new RuntimeException("Failed to generate Request class: {$requestName}");
        }

        return $requestName;
    }
}
