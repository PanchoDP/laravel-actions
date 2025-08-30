<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\GenerateRequest;
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
    {--u : Make a User injection}
    {--r : Generate a Request class and inject it into the action}
    {--tu : Make a DB transaction action with User injection}
    {--ut : Make a DB transaction action with User injection}
    {--tr : Make a DB transaction action with Request injection}
    {--rt : Make a DB transaction action with Request injection}
    {--ur : Make a User injection with Request injection}
    {--ru : Make a User injection with Request injection}
    {--tur : Make a DB transaction action with User and Request injection}
    {--tru : Make a DB transaction action with User and Request injection}
    {--utr : Make a DB transaction action with User and Request injection}
    {--urt : Make a DB transaction action with User and Request injection}
    {--rtu : Make a DB transaction action with User and Request injection}
    {--rut : Make a DB transaction action with User and Request injection}';

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

            // Generate Request class if --r flag is present
            if ($config['rFlag'] ?? false) {
                $this->generateRequestFile($config);
            }

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
     *
     * @return array<string, mixed>
     */
    private function processInputs(): array
    {
        $name = $this->argument('name');
        $name = is_string($name) ? mb_trim($name) : '';

        $subfolder = $this->argument('subfolder');
        $subfolder = is_string($subfolder) ? mb_trim($subfolder, '/\\') : '';

        // Check for combination flags first
        $turFlag = (bool) ($this->option('tur') || $this->option('tru') ||
                          $this->option('utr') || $this->option('urt') ||
                          $this->option('rtu') || $this->option('rut'));

        $tuFlag = (bool) ($this->option('tu') || $this->option('ut') || $turFlag);
        $trFlag = (bool) ($this->option('tr') || $this->option('rt') || $turFlag);
        $urFlag = (bool) ($this->option('ur') || $this->option('ru') || $turFlag);

        // Individual flags or derived from combinations
        $tFlag = (bool) ($this->option('t') || $tuFlag || $trFlag);
        $uFlag = (bool) ($this->option('u') || $tuFlag || $urFlag);
        $rFlag = (bool) ($this->option('r') || $trFlag || $urFlag);

        return [
            'name' => $name,
            'subfolder' => $subfolder,
            'tFlag' => $tFlag,
            'uFlag' => $uFlag,
            'rFlag' => $rFlag,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
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
     * @param  array<string, mixed>  $config
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
     * @param  array<string, mixed>  $config
     */
    private function generateRequestFile(array $config): void
    {
        $filename = is_string($config['filename']) ? $config['filename'] : '';

        $requestName = GenerateRequest::handle($filename);
        $this->info("Request {$requestName} created successfully...");
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function generateActionFile(array $config): void
    {
        $tFlag = is_bool($config['tFlag']) ? $config['tFlag'] : false;
        $uFlag = is_bool($config['uFlag']) ? $config['uFlag'] : false;
        $rFlag = is_bool($config['rFlag']) ? $config['rFlag'] : false;
        $filename = is_string($config['filename']) ? $config['filename'] : '';
        $namespace = is_string($config['namespace']) ? $config['namespace'] : '';
        $path = is_string($config['path']) ? $config['path'] : '';

        $stub = PrepareStub::handle(
            $tFlag,
            $uFlag,
            $rFlag,
            $filename,
            $namespace
        );

        File::put($path, $stub);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function displaySuccessMessages(array $config): void
    {
        $tFlag = is_bool($config['tFlag']) ? $config['tFlag'] : false;
        $uFlag = is_bool($config['uFlag']) ? $config['uFlag'] : false;
        $rFlag = is_bool($config['rFlag']) ? $config['rFlag'] : false;
        $filename = is_string($config['filename']) ? $config['filename'] : '';
        $relativePath = is_string($config['relative_path']) ? $config['relative_path'] : '';

        $features = [];
        if ($tFlag) {
            $features[] = 'DB transaction';
        }
        if ($uFlag) {
            $features[] = 'User injection';
        }
        if ($rFlag) {
            $features[] = 'Request injection';
        }

        $featuresText = empty($features) ? '.' : ' with '.implode(', ', $features).'.';
        $this->info("Action {$filename} created successfully at app/{$relativePath} folder{$featuresText}");
    }
}
