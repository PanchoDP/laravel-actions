<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Throwable;

final class GenerateRequest
{
    /**
     * Generate a Laravel Request class using Artisan command
     *
     * @param  string  $actionName  The base name for the action (e.g., 'Limpiar')
     *
     * @throws InvalidArgumentException
     */
    public static function handle(string $actionName): string
    {
        if (empty($actionName)) {
            throw new InvalidArgumentException('Action name cannot be empty.');
        }

        // Validate action name format
        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $actionName)) {
            throw new InvalidArgumentException('Invalid action name format. Must start with uppercase letter and contain only alphanumeric characters.');
        }

        $requestName = $actionName.'Request';

        try {
            // Execute Laravel's make:request command
            $exitCode = Artisan::call('make:request', [
                'name' => $requestName,
            ]);

            if ($exitCode !== 0) {
                throw new InvalidArgumentException("Failed to generate Request class: {$requestName}");
            }

            return $requestName;
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Error generating Request class: '.$e->getMessage());
        }
    }
}
