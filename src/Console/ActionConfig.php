<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

final readonly class ActionConfig
{
    public function __construct(
        public string $name,
        public string $subfolder,
        public string $baseFolder,
        public string $methodName,
        public string $folderPath,
        public string $path,
        public string $namespace,
        public string $relativePath,
        public string $filename,
        public bool $transaction,
        public bool $user,
        public bool $request,
        public bool $static,
        public bool $force,
    ) {}
}
