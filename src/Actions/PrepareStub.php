<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;

final class PrepareStub
{
    public static function handle(
        bool $tFlag,
        bool $uFlag,
        bool $rFlag,
        bool $sFlag,
        string $filename,
        string $namespace,
        string $methodName = 'handle',
    ): string {
        $stubFile = self::selectStubFile($tFlag, $rFlag);

        if (! is_readable($stubFile)) {
            throw new RuntimeException("Stub file not found or not readable: {$stubFile}");
        }

        $stub = file_get_contents($stubFile);

        if ($stub === false) {
            throw new RuntimeException("Failed to read stub file: {$stubFile}");
        }

        return strtr($stub, [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $filename,
            '{{ method }}' => $methodName,
            '{{ name_action }}' => ucfirst($methodName),
            '{{ method_type }}' => $sFlag ? 'static ' : '',
            '{{ import_model }}' => $uFlag ? 'use App\Models\User;' : '',
            '{{ user }}' => $uFlag ? 'User $user,' : '',
            '{{ request_class }}' => $rFlag ? $filename.'Request' : '',
        ]);
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
}
