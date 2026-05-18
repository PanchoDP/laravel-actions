<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class ObtainNamespace
{
    public static function handle(?string $subfolder, string $base_folder): string
    {
        /** @var string $appNamespace */
        $appNamespace = app()->getNamespace();

        $subfolder = str_replace('/', '\\', $subfolder ?? '');
        $parts = array_filter([$base_folder, $subfolder], static fn (string $part): bool => $part !== '');

        return $appNamespace.implode('\\', $parts);
    }
}
