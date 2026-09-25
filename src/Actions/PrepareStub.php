<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;

final class PrepareStub
{
    public static function handle(
        string $class,
        string $namespace,
        string $methodName = 'handle',
        bool $transaction = false,
        bool $user = false,
        bool $request = false,
        bool $static = false,
    ): string {
        $stubFile = self::selectStubFile($transaction, $request);

        if (! is_readable($stubFile)) {
            throw new RuntimeException("Stub file not found or not readable: {$stubFile}");
        }

        $stub = file_get_contents($stubFile);

        if ($stub === false) {
            throw new RuntimeException("Failed to read stub file: {$stubFile}");
        }

        /** @var string $rootNamespace */
        $rootNamespace = app()->getNamespace();

        $rendered = strtr($stub, [
            '{{ namespace }}' => $namespace,
            '{{ root_namespace }}' => $rootNamespace,
            '{{ class }}' => $class,
            '{{ method }}' => $methodName,
            '{{ method_type }}' => $static ? 'static ' : '',
            '{{ import_model }}' => $user ? "use {$rootNamespace}Models\\User;" : '',
            '{{ user }}' => $user ? 'User $user, ' : '',
            '{{ request_class }}' => $request ? GenerateRequest::requestNameFor($class) : '',
        ]);

        return self::normalize($rendered);
    }

    /**
     * Clean up the gaps left by empty placeholders so the generated file is
     * PSR-12 compliant: no runs of blank lines and a single trailing newline.
     */
    private static function normalize(string $stub): string
    {
        $stub = str_replace(["\r\n", "\r"], "\n", $stub);
        $stub = preg_replace("/\n{3,}/", "\n\n", $stub) ?? $stub;
        $stub = preg_replace("/[ \t]+$/m", '', $stub) ?? $stub;

        return mb_rtrim($stub, "\n")."\n";
    }

    /**
     * Select the appropriate stub file based on flags.
     * Checks for published user stubs first, falls back to package stubs.
     */
    private static function selectStubFile(bool $transaction, bool $request): string
    {
        if ($transaction && $request) {
            $stubName = 'action_transaction_request.stub';
        } elseif ($request) {
            $stubName = 'action_request.stub';
        } elseif ($transaction) {
            $stubName = 'action_transaction.stub';
        } else {
            $stubName = 'action.stub';
        }

        $publishedStub = resource_path('stubs/vendor/laravel-actions/'.$stubName);

        return file_exists($publishedStub) ? $publishedStub : __DIR__.'/../../stubs/'.$stubName;
    }
}
