<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class ParseActionPath
{
    /**
     * Normalize Laravel-style path syntax in the action name.
     *
     * Supports forward and backward slashes inside the name argument
     * (e.g. "User/Auth/Login") and merges any path segments with the
     * explicit subfolder argument.
     *
     * @return array{name: string, subfolder: string}
     */
    public static function handle(string $name, string $subfolder): array
    {
        $subfolder = mb_trim($subfolder, '/\\');

        if (! preg_match('#[/\\\\]#', $name)) {
            return ['name' => $name, 'subfolder' => $subfolder];
        }

        $parts = preg_split('#[/\\\\]+#', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) < 2) {
            return ['name' => $name, 'subfolder' => $subfolder];
        }

        $className = array_pop($parts);
        $pathFromName = implode('/', $parts);
        $merged = $subfolder === '' ? $pathFromName : $pathFromName.'/'.$subfolder;

        return ['name' => $className, 'subfolder' => $merged];
    }
}
