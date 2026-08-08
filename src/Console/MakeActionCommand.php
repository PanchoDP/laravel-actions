<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\GenerateRequest;
use Panchodp\LaravelAction\Actions\ObtainNamespace;
use Panchodp\LaravelAction\Actions\ParseActionPath;
use Panchodp\LaravelAction\Actions\PreparePath;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;
use Panchodp\LaravelAction\Actions\ValidateFolder;
use Panchodp\LaravelAction\Actions\ValidateName;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

/**
 * @phpstan-type RawInput array{
 *     name: string,
 *     subfolder: string,
 *     transaction: bool,
 *     user: bool,
 *     request: bool,
 *     static: bool,
 *     force: bool,
 * }
 */
final class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name?} {subfolder?}
    {--t|transaction : Wrap the action in a DB transaction}
    {--u|user : Inject the authenticated User}
    {--r|request : Generate a Request class and inject it into the action}
    {--s|static : Make the method static}
    {--force : Overwrite the action if it already exists}';

    protected $description = 'Create a new action class';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        try {
            $config = $this->buildConfig($this->processInputs());
            $this->createDirectoryStructure($config);

            if ($config->request) {
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
     * @return RawInput
     */
    private function processInputs(): array
    {
        $nameArg = $this->argument('name');
        $name = is_string($nameArg) ? mb_trim($nameArg) : '';

        if ($name === '') {
            if (! $this->input->isInteractive()) {
                throw new InvalidArgumentException('Action name is required.');
            }

            return $this->askInteractive();
        }

        $subfolderArg = $this->argument('subfolder');
        $subfolder = is_string($subfolderArg) ? $subfolderArg : '';

        $parsed = ParseActionPath::handle($name, $subfolder);

        return [
            'name' => $parsed['name'],
            'subfolder' => $parsed['subfolder'],
            'transaction' => (bool) $this->option('transaction'),
            'user' => (bool) $this->option('user'),
            'request' => (bool) $this->option('request'),
            'static' => (bool) $this->option('static'),
            'force' => (bool) $this->option('force'),
        ];
    }

    /**
     * @param  RawInput  $input
     */
    private function buildConfig(array $input): ActionConfig
    {
        ValidateName::handle($input['name']);

        $baseFolderConfig = config('laravel-actions.base_folder');
        $methodNameConfig = config('laravel-actions.method_name');
        $validated = ValidateConfiguration::handle(
            is_string($baseFolderConfig) ? $baseFolderConfig : null,
            is_string($methodNameConfig) ? $methodNameConfig : null,
        );

        $folders = PrepareSubfolder::handle($input['subfolder']);
        ValidateFolder::handle($folders);
        $folderPath = implode(DIRECTORY_SEPARATOR, $folders);

        // Validated up front so an unusable Request name never leaves a
        // half-created directory structure behind.
        if ($input['request']) {
            GenerateRequest::requestNameFor($input['name']);
        }

        $path = PreparePath::handle($folderPath, $input['name'], $validated['base_folder'], $input['force']);
        $namespace = ObtainNamespace::handle($folderPath, $validated['base_folder']);
        $relativePath = dirname("{$validated['base_folder']}/{$folderPath}/{$input['name']}.php");

        return new ActionConfig(
            name: $input['name'],
            subfolder: $input['subfolder'],
            baseFolder: $validated['base_folder'],
            methodName: $validated['method_name'],
            folderPath: $folderPath,
            path: $path,
            namespace: $namespace,
            relativePath: $relativePath,
            filename: pathinfo($path, PATHINFO_FILENAME),
            transaction: $input['transaction'],
            user: $input['user'],
            request: $input['request'],
            static: $input['static'],
            force: $input['force'],
        );
    }

    private function createDirectoryStructure(ActionConfig $config): void
    {
        if (CreateDirectory::handle($config->path)) {
            $this->info("Directory {$config->relativePath} created successfully...");
        }
    }

    private function generateRequestFile(ActionConfig $config): void
    {
        $requestName = GenerateRequest::handle($config->filename, $config->force);
        $this->info("Request {$requestName} created successfully...");
    }

    private function generateActionFile(ActionConfig $config): void
    {
        $stub = PrepareStub::handle(
            $config->transaction,
            $config->user,
            $config->request,
            $config->static,
            $config->filename,
            $config->namespace,
            $config->methodName,
        );

        if (File::put($config->path, $stub) === false) {
            throw new RuntimeException("Failed to write the action file at {$config->path}. Check the directory permissions.");
        }
    }

    /**
     * @return RawInput
     */
    private function askInteractive(): array
    {
        $name = text(
            label: 'Action name',
            placeholder: 'e.g. CreateUser',
            required: true,
        );

        $subfolder = text(
            label: 'Subfolder (optional)',
            placeholder: 'e.g. User or User/Auth',
        );

        return [
            'name' => mb_trim($name),
            'subfolder' => mb_trim($subfolder, '/\\'),
            'transaction' => confirm(label: 'Include DB transaction?', default: false),
            'user' => confirm(label: 'Inject User?', default: false),
            'request' => confirm(label: 'Generate Request class?', default: false),
            'static' => confirm(label: 'Static method?', default: false),
            'force' => false,
        ];
    }

    private function displaySuccessMessages(ActionConfig $config): void
    {
        $features = array_keys(array_filter([
            'DB transaction' => $config->transaction,
            'User injection' => $config->user,
            'Request injection' => $config->request,
            'static method' => $config->static,
        ]));

        $featuresText = $features === [] ? '.' : ' with '.implode(', ', $features).'.';
        $this->info("Action {$config->filename} created successfully at app/{$config->relativePath} folder{$featuresText}");
    }
}
