<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;
use Throwable;

final class PrepareStub
{
    public static function handle(bool $tFlag, bool $uFlag, bool $rFlag, string $filename, string $namespace): string
    {
        try {
            // Select appropriate stub based on flags
            $stubFile = self::selectStubFile($tFlag, $rFlag);

            // Security: Validate stub file exists before reading
            if (! file_exists($stubFile) || ! is_readable($stubFile)) {
                throw new RuntimeException("Stub file not found or not readable: {$stubFile}");
            }

            $stub = file_get_contents($stubFile);

            if ($stub === false) {
                throw new RuntimeException("Failed to read stub file: {$stubFile}");
            }

            // Handle Request class injection
            if ($rFlag) {
                $requestClass = $filename.'Request';
                $safeRequestClass = self::sanitizeForTemplate($requestClass);
                $stub = str_replace('{{ request_class }}', $safeRequestClass, $stub);
            }

            if ($uFlag) {
                $stub = str_replace('{{ import_model }}', 'use App\Models\User;', $stub);
                $stub = str_replace('{{ user }}', 'User $user,', $stub);
            } else {
                $stub = str_replace('{{ import_model }}', '', $stub);
                $stub = str_replace('{{ user }}', '', $stub);
            }

            // Security: Sanitize filename and namespace for template injection
            $safeFilename = self::sanitizeForTemplate($filename);
            $safeNamespace = self::sanitizeForTemplate($namespace, true); // Allow backslashes for namespaces

            $stub = str_replace('{{ class }}', $safeFilename, $stub);
            $stub = str_replace('{{ namespace }}', $safeNamespace, $stub);

            $methodName = is_string(config('laravel-actions.method_name', 'handle')) ? config('laravel-actions.method_name', 'handle') : 'handle';
            $safeMethodName = self::sanitizeForTemplate($methodName);
            $stub = str_replace('{{ method }}', $safeMethodName, $stub);
            $stub = str_replace('{{ name_action }}', ucfirst($safeMethodName), $stub);

        } catch (Throwable $e) {
            throw new RuntimeException('Error preparing the stub: '.$e->getMessage());
        }

        return $stub;

    }

    /**
     * Select the appropriate stub file based on flags
     */
    private static function selectStubFile(bool $tFlag, bool $rFlag): string
    {
        $baseDir = __DIR__.'/../stubs/';

        if ($tFlag && $rFlag) {
            return $baseDir.'action_transaction_request.stub';
        }

        if ($rFlag) {
            return $baseDir.'action_request.stub';
        }

        if ($tFlag) {
            return $baseDir.'action_transaction.stub';
        }

        return $baseDir.'action.stub';
    }

    /**
     * Sanitize input for template usage to prevent template injection.
     *
     * @param  bool  $allowBackslashes  Allow backslashes (for namespaces)
     */
    private static function sanitizeForTemplate(string $input, bool $allowBackslashes = false): string
    {
        if ($allowBackslashes) {
            // Remove potential PHP tags and dangerous characters but allow backslashes for namespaces
            $sanitized = preg_replace('/[<>\'"`\$]/', '', $input);
        } else {
            // Remove potential PHP tags and dangerous characters including backslashes
            $sanitized = preg_replace('/[<>\'"`\$\\\]/', '', $input);
        }

        // Ensure the result is still valid for its intended use
        if ($sanitized === null) {
            throw new RuntimeException('Input sanitization failed');
        }

        return $sanitized;
    }
}
