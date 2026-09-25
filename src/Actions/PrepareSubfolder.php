<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;

final class PrepareSubfolder
{
    /**
     * Split the subfolder string on forward or back slashes into its folder names.
     *
     * @param  string  $subfolder  The subfolder string to process.
     * @return array<string> An array of subfolder names.
     *
     * @throws InvalidArgumentException
     */
    public static function handle(string $subfolder): array
    {
        if ($subfolder === '' || $subfolder === '0') {
            return [];
        }

        if (PathTraversalGuard::check($subfolder)) {
            throw new InvalidArgumentException('Invalid subfolder path: path traversal sequences are not allowed.');
        }

        $segments = preg_split('#[/\\\\]+#', $subfolder, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Surrounding spaces are forgiven; spaces inside a segment are left for ValidateFolder to reject.
        return array_values(array_filter(
            array_map(mb_trim(...), $segments),
            static fn (string $segment): bool => $segment !== '',
        ));
    }
}
