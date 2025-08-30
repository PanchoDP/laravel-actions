<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\ObtainNamespace;
use Panchodp\LaravelAction\Actions\PreparePath;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;
use Panchodp\LaravelAction\Actions\ValidateFolder;
use Panchodp\LaravelAction\Actions\ValidateName;
use Throwable;

final class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name} {subfolder?} 
    {--t : Make a DB transaction action } 
    {--tu : Make a DB transaction action } 
    {--ut : Make a DB transaction action } 
    {--u : Make a User injection}';

    protected $description = 'Create a new action class';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        try {
            $input = $this->processInputs();
            $config = $this->validateAndPrepareConfig($input);
            $this->createDirectoryStructure($config);
            $this->generateActionFile($config);
            $this->displaySuccessMessages($config);

            return 0;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * Process and sanitize command inputs.
     * @return array<string, mixed>
     */
    private function processInputs(): array
    {
        $name = $this->argument('name');
        $name = is_string($name) ? mb_trim($name) : '';

        $subfolder = $this->argument('subfolder');
        $subfolder = is_string($subfolder) ? mb_trim($subfolder, '/\\') : '';

        $tuFlag = (bool) ($this->option('tu') || $this->option('ut'));
        $tFlag = (bool) ($this->option('t') || $tuFlag);
        $uFlag = (bool) ($this->option('u') || $tuFlag);

        return [
            'name' => $name,
            'subfolder' => $subfolder,
            'tuFlag' => $tuFlag,
            'tFlag' => $tFlag,
            'uFlag' => $uFlag,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function validateAndPrepareConfig(array $input): array
    {
        $name = is_string($input['name']) ? $input['name'] : '';
        $subfolder = is_string($input['subfolder']) ? $input['subfolder'] : '';
        
        ValidateName::handle($name);

        // Security: Validate configuration values
        $baseFolder = config('laravel-actions.base_folder');
        $methodName = config('laravel-actions.method_name');
        $validatedConfig = ValidateConfiguration::handle(
            is_string($baseFolder) ? $baseFolder : null,
            is_string($methodName) ? $methodName : null
        );

        $folders = PrepareSubfolder::handle($subfolder);
        ValidateFolder::handle($folders);

        $folder_path = implode(DIRECTORY_SEPARATOR, $folders);
        $path = PreparePath::handle($folder_path, $name, $validatedConfig['base_folder']);
        $namespace = ObtainNamespace::handle($folder_path, $name, $validatedConfig['base_folder']);
        $relative_path = dirname("{$validatedConfig['base_folder']}/$folder_path/{$name}.php");

        return array_merge($input, [
            'base_folder' => $validatedConfig['base_folder'],
            'folder_path' => $folder_path,
            'path' => $path,
            'namespace' => $namespace,
            'relative_path' => $relative_path,
            'filename' => pathinfo($path, PATHINFO_FILENAME),
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createDirectoryStructure(array $config): void
    {
        $permissions = is_int(config('laravel-actions.directory_permissions'))
            ? config('laravel-actions.directory_permissions')
            : 0750;

        $path = is_string($config['path']) ? $config['path'] : '';
        $relativePath = is_string($config['relative_path']) ? $config['relative_path'] : '';
        
        CreateDirectory::handle($path, $permissions);
        $this->info("Directory {$relativePath} created successfully...");
    }

    /**
     * @param array<string, mixed> $config
     */
    private function generateActionFile(array $config): void
    {
        $tuFlag = is_bool($config['tuFlag']) ? $config['tuFlag'] : false;
        $tFlag = is_bool($config['tFlag']) ? $config['tFlag'] : false;
        $uFlag = is_bool($config['uFlag']) ? $config['uFlag'] : false;
        $filename = is_string($config['filename']) ? $config['filename'] : '';
        $namespace = is_string($config['namespace']) ? $config['namespace'] : '';
        $path = is_string($config['path']) ? $config['path'] : '';
        
        $stub = PrepareStub::handle(
            $tuFlag,
            $tFlag,
            $uFlag,
            $filename,
            $namespace
        );

        File::put($path, $stub);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function displaySuccessMessages(array $config): void
    {
        $tFlag = is_bool($config['tFlag']) ? $config['tFlag'] : false;
        $filename = is_string($config['filename']) ? $config['filename'] : '';
        $relativePath = is_string($config['relative_path']) ? $config['relative_path'] : '';
        
        $transaction = $tFlag ? ' with DB transaction' : '.';
        $this->info("Action {$filename} created successfully at app/{$relativePath} folder".$transaction);
    }
}
