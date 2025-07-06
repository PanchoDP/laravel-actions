<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class ObtainNamespace
{
    public static function handle(?string $subfolder, string $name): string
    {
        if (empty($subfolder)) {
            return 'App\\Actions';
        }
        $relative_path = dirname("Actions/{$subfolder}/{$name}.php");
        $namespace_type = str_replace('/', '\\', $relative_path);

        return mb_rtrim(app()->getNamespace().$namespace_type);

    }
}
