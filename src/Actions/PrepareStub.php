<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use RuntimeException;
use Throwable;

final class PrepareStub
{
    public static function handle(bool $tuFlag, bool $tFlag, bool $uFlag, string $filename, string $namespace): string
    {
        try {
            ($tFlag) ? $stub = file_get_contents(__DIR__.'/../stubs/action_transaction.stub')
                : $stub = file_get_contents(__DIR__.'/../stubs/action.stub');

            if ($uFlag) {
                $stub = str_replace('{{ import_model }}', 'use App\Models\User;', (string) $stub);
                $stub = str_replace('{{ param }}', ' * @param User $user', (string) $stub);
                $stub = str_replace('{{ user }}', 'User $user,', (string) $stub);
            } else {
                $stub = str_replace('{{ import_model }}', '', (string) $stub);
                $stub = str_replace('{{ param }}', '*', (string) $stub);
                $stub = str_replace('{{ user }}', '', (string) $stub);
            }

            $stub = str_replace('{{ class }}', $filename, (string) $stub);
            $stub = str_replace('{{ namespace }}', $namespace, (string) $stub);

            $methodName = is_string(config('laravel-actions.method_name', 'handle')) ? config('laravel-actions.method_name', 'handle') : '';
            $stub = str_replace('{{ method }}', $methodName, (string) $stub);
            $stub = str_replace('{{ name_action }}', ucfirst($methodName), (string) $stub);

        } catch (Throwable $e) {
            throw new RuntimeException('Error preparing the stub: '.$e->getMessage());
        }

        return $stub;

    }
}
