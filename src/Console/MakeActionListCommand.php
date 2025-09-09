<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class MakeActionListCommand extends Command
{
    protected $signature = 'actions:list';

    protected $description = 'Display a tree view of all actions in the Actions directory';

    public function handle(): int
    {
        $actionsPath = $this->getActionsPath();

        if (! File::exists($actionsPath)) {
            $this->error("Actions directory not found: {$actionsPath}");

            return 1;
        }

        $this->info('Actions Directory Tree:');
        $this->line('');
        $this->displayTree($actionsPath);

        return 0;
    }

    private function getActionsPath(): string
    {
        $baseFolder = config('laravel-actions.base_folder', 'Actions');
        assert(is_string($baseFolder));

        return app_path($baseFolder);
    }

    private function displayTree(string $path, string $prefix = '', bool $isLast = true, int $depth = 0): void
    {
        $baseName = basename($path);
        $connector = $isLast ? '└── ' : '├── ';

        if ($depth === 0) {
            $this->line("<fg=green>{$baseName}/</>");
        } else {
            if (is_dir($path)) {
                $this->line("{$prefix}{$connector}<fg=green>{$baseName}/</>");
            } else {
                $className = $this->extractClassName($path);
                $this->line("{$prefix}{$connector}<fg=cyan>{$className}</>");
            }
        }

        if (is_dir($path)) {
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
     * @return array<string>
     */
    private function getDirectoryContents(string $path): array
    {
        $items = [];

        if (! is_readable($path)) {
            return $items;
        }

        $files = scandir($path);
        if ($files === false) {
            return $items;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$file;

            if (is_dir($fullPath) || $this->isPhpActionFile($fullPath)) {
                $items[] = $fullPath;
            }
        }

        // Sort: directories first, then files
        usort($items, function ($a, $b) {
            $aIsDir = is_dir($a);
            $bIsDir = is_dir($b);

            if ($aIsDir && ! $bIsDir) {
                return -1;
            }
            if (! $aIsDir && $bIsDir) {
                return 1;
            }

            return strcasecmp(basename($a), basename($b));
        });

        return $items;
    }

    private function isPhpActionFile(string $path): bool
    {
        return is_file($path) &&
               pathinfo($path, PATHINFO_EXTENSION) === 'php' &&
               $this->containsActionClass($path);
    }

    private function containsActionClass(string $path): bool
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            return false;
        }

        return str_contains($content, 'class ');
    }

    private function extractClassName(string $path): string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            return pathinfo($path, PATHINFO_FILENAME);
        }

        if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
            return $matches[1];
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }
}
