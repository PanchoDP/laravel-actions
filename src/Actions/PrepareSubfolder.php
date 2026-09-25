<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

final class PrepareSubfolder
{
    /**
     * Split the subfolder string on forward or back slashes into its folder names.
     *
     * Segments are not validated here: ValidateFolder rejects anything that is
     * not a plain identifier, which includes "..", "." and drive letters.
     *
     * @param  string  $subfolder  The subfolder string to process.
     * @return array<string> An array of subfolder names.
     */
    public static function handle(string $subfolder): array
    {
        if ($subfolder === '' || $subfolder === '0') {
            return [];
        }

        $segments = preg_split('#[/\\\\]+#', $subfolder, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Surrounding spaces are forgiven; spaces inside a segment are left for ValidateFolder to reject.
        return array_values(array_filter(
            array_map(mb_trim(...), $segments),
            static fn (string $segment): bool => $segment !== '',
        ));
    }
}
