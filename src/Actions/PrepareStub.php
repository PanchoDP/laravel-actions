<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;
use Throwable;

final class PrepareStub
{
    public static function handle(bool $tFlag, bool $uFlag, bool $rFlag, bool $sFlag, string $filename, string $namespace): string
    {
        try {
            $stubFile = self::selectStubFile($tFlag, $rFlag);

            if (! file_exists($stubFile) || ! is_readable($stubFile)) {
                throw new RuntimeException("Stub file not found or not readable: {$stubFile}");
            }

            $stub = file_get_contents($stubFile);

            if ($stub === false) {
                throw new RuntimeException("Failed to read stub file: {$stubFile}");
            }

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

            $safeFilename = self::sanitizeForTemplate($filename);
            $safeNamespace = self::sanitizeForTemplate($namespace, true); // Allow backslashes for namespaces

            $stub = str_replace('{{ class }}', $safeFilename, $stub);
            $stub = str_replace('{{ namespace }}', $safeNamespace, $stub);

            $methodType = $sFlag ? 'static ' : '';
            $stub = str_replace('{{ method_type }}', $methodType, $stub);

            $methodName = is_string(config('laravel-actions.method_name', 'handle')) ? config('laravel-actions.method_name', 'handle') : 'handle';
            $safeMethodName = self::sanitizeForTemplate($methodName);
            $stub = str_replace('{{ method }}', $safeMethodName, $stub);
            $stub = str_replace('{{ name_action }}', ucfirst($safeMethodName), $stub);

        } catch (Throwable $e) {
            throw new RuntimeException('Error preparing the stub: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $stub;

    }

    /**
     * Select the appropriate stub file based on flags.
     * Checks for published user stubs first, falls back to package stubs.
     */
    private static function selectStubFile(bool $tFlag, bool $rFlag): string
    {
        if ($tFlag && $rFlag) {
            $stubName = 'action_transaction_request.stub';
        } elseif ($rFlag) {
            $stubName = 'action_request.stub';
        } elseif ($tFlag) {
            $stubName = 'action_transaction.stub';
        } else {
            $stubName = 'action.stub';
        }

        $publishedStub = resource_path('stubs/vendor/laravel-actions/'.$stubName);

        return file_exists($publishedStub) ? $publishedStub : __DIR__.'/../../stubs/'.$stubName;
    }

    /**
     * Sanitize input for template usage to prevent template injection.
     *
     * @param  bool  $allowBackslashes  Allow backslashes (for namespaces)
     */
    private static function sanitizeForTemplate(string $input, bool $allowBackslashes = false): string
    {
        $sanitized = $allowBackslashes ? preg_replace('/[<>\'"`\$]/', '', $input) : preg_replace('/[<>\'"`\$\\\]/', '', $input);

        if ($sanitized === null) {
            throw new RuntimeException('Input sanitization failed');
        }

        return $sanitized;
    }
}
