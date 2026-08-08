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

        $rendered = strtr($stub, [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $filename,
            '{{ method }}' => $methodName,
            '{{ name_action }}' => ucfirst($methodName),
            '{{ method_type }}' => $sFlag ? 'static ' : '',
            '{{ import_model }}' => $uFlag ? 'use App\Models\User;' : '',
            '{{ user }}' => $uFlag ? 'User $user, ' : '',
            '{{ request_class }}' => $rFlag ? $filename.'Request' : '',
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
