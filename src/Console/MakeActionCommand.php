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

    private function processInputs(): array
    {
        $name = (string) $this->argument('name');
        $name = mb_trim($name);

        $subfolder = (string) $this->argument('subfolder');
        $subfolder = mb_trim($subfolder, '/\\');

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

    private function validateAndPrepareConfig(array $input): array
    {
        ValidateName::handle($input['name']);

        // Security: Validate configuration values
        $validatedConfig = ValidateConfiguration::handle(
            config('laravel-actions.base_folder'),
            config('laravel-actions.method_name')
        );

        $folders = PrepareSubfolder::handle($input['subfolder']);
        ValidateFolder::handle($folders);

        $folder_path = implode(DIRECTORY_SEPARATOR, $folders);
        $path = PreparePath::handle($folder_path, $input['name'], $validatedConfig['base_folder']);
        $namespace = ObtainNamespace::handle($folder_path, $input['name'], $validatedConfig['base_folder']);
        $relative_path = dirname("{$validatedConfig['base_folder']}/$folder_path/{$input['name']}.php");

        return array_merge($input, [
            'base_folder' => $validatedConfig['base_folder'],
            'folder_path' => $folder_path,
            'path' => $path,
            'namespace' => $namespace,
            'relative_path' => $relative_path,
            'filename' => pathinfo($path, PATHINFO_FILENAME),
        ]);
    }

    private function createDirectoryStructure(array $config): void
    {
        $permissions = is_int(config('laravel-actions.directory_permissions'))
            ? config('laravel-actions.directory_permissions')
            : 0750;

        CreateDirectory::handle($config['path'], $permissions);
        $this->info("Directory {$config['relative_path']} created successfully...");
    }

    private function generateActionFile(array $config): void
    {
        $stub = PrepareStub::handle(
            $config['tuFlag'],
            $config['tFlag'],
            $config['uFlag'],
            $config['filename'],
            $config['namespace']
        );

        File::put($config['path'], $stub);
    }

    private function displaySuccessMessages(array $config): void
    {
        $transaction = $config['tFlag'] ? ' with DB transaction' : '.';
        $this->info("Action {$config['filename']} created successfully at app/{$config['relative_path']} folder".$transaction);
    }
}
