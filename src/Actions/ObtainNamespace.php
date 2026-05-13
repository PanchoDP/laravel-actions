<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class ObtainNamespace
{
    public static function handle(?string $subfolder, string $name, string $base_folder): string
    {
        /** @var string $appNamespace */
        $appNamespace = app()->getNamespace();

        if (in_array($subfolder, [null, '', '0'], true)) {
            return rtrim($appNamespace.$base_folder);
        }
        $relative_path = dirname("{$base_folder}/{$subfolder}/{$name}.php");
        $namespace_type = str_replace('/', '\\', $relative_path);

        return rtrim($appNamespace.$namespace_type);

    }
}
