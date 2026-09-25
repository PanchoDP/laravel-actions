<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;
use Symfony\Component\Finder\SplFileInfo;

final class ListActionsCommand extends Command
{
    protected $signature = 'actions:list';

    protected $description = 'Display a tree view of all actions in the Actions directory';

    public function handle(): int
    {
        try {
            $actionsPath = $this->getActionsPath();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (! File::exists($actionsPath)) {
            $this->error("Actions directory not found: {$actionsPath}");

            return self::FAILURE;
        }

        $this->info('Actions Directory Tree:');
        $this->newLine();
        $this->displayTree($actionsPath);

        return self::SUCCESS;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getActionsPath(): string
    {
        return app_path(ValidateConfiguration::configuredBaseFolder());
    }

    private function displayTree(string $path, string $prefix = '', bool $isLast = true, int $depth = 0): void
    {
        $baseName = basename($path);
        $connector = $isLast ? '└── ' : '├── ';

        if ($depth === 0) {
            $this->line("<fg=green>{$baseName}/</>");
        } elseif (File::isDirectory($path)) {
            $this->line("{$prefix}{$connector}<fg=green>{$baseName}/</>");
        } else {
            $className = pathinfo($path, PATHINFO_FILENAME);
            $this->line("{$prefix}{$connector}<fg=cyan>{$className}</>");
        }

        if (File::isDirectory($path)) {
            $items = $this->getDirectoryContents($path);
            $totalItems = count($items);

            foreach ($items as $index => $item) {
                $itemIsLast = ($index === $totalItems - 1);
                $newPrefix = $depth === 0 ? '' : $prefix.($isLast ? '    ' : '│   ');

                $this->displayTree($item, $newPrefix, $itemIsLast, $depth + 1);
            }
        }
    }

    /**
     * Subdirectories first, then PHP files, each group sorted case-insensitively.
     *
     * @return list<string>
     */
    private function getDirectoryContents(string $path): array
    {
        if (! File::isReadable($path)) {
            return [];
        }

        // File::directories() is typed as a plain array; keep only the string paths it returns.
        $directories = array_values(array_filter(File::directories($path), is_string(...)));

        $files = array_map(
            static fn (SplFileInfo $file): string => $file->getPathname(),
            array_filter(File::files($path), static fn (SplFileInfo $file): bool => $file->getExtension() === 'php'),
        );

        $byName = static fn (string $a, string $b): int => strcasecmp(basename($a), basename($b));
        usort($directories, $byName);
        usort($files, $byName);

        return [...$directories, ...$files];
    }
}
