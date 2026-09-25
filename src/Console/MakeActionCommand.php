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
        $name = $this->stringArgument('name');
        $subfolder = $this->stringArgument('subfolder');
        $interactive = false;

        if ($name === '') {
            if (! $this->input->isInteractive()) {
                throw new InvalidArgumentException('Action name is required.');
            }

            $name = mb_trim(text(
                label: 'Action name',
                placeholder: 'e.g. CreateUser or User/CreateUser',
                required: true,
            ));

            $subfolder = text(
                label: 'Subfolder (optional)',
                placeholder: 'e.g. User or User/Auth',
            );

            $interactive = true;
        }

        $parsed = ParseActionPath::handle($name, $subfolder);

        return [
            'name' => $parsed['name'],
            'subfolder' => $parsed['subfolder'],
            'transaction' => $this->flag('transaction', 'Include DB transaction?', $interactive),
            'user' => $this->flag('user', 'Inject User?', $interactive),
            'request' => $this->flag('request', 'Generate Request class?', $interactive),
            'static' => $this->flag('static', 'Static method?', $interactive),
            'force' => (bool) $this->option('force'),
        ];
    }

    private function stringArgument(string $key): string
    {
        $value = $this->argument($key);

        return is_string($value) ? mb_trim($value) : '';
    }

    /**
     * A flag passed on the command line always wins; otherwise ask only when prompting.
     */
    private function flag(string $option, string $question, bool $ask): bool
    {
        if ((bool) $this->option($option)) {
            return true;
        }

        return $ask && confirm(label: $question, default: false);
    }

    /**
     * @param  RawInput  $input
     */
    private function buildConfig(array $input): ActionConfig
    {
        ValidateName::handle($input['name']);

        $baseFolder = ValidateConfiguration::configuredBaseFolder();
        $methodName = ValidateConfiguration::configuredMethodName();

        $folders = PrepareSubfolder::handle($input['subfolder']);
        ValidateFolder::handle($folders);
        $folderPath = implode(DIRECTORY_SEPARATOR, $folders);

        $path = PreparePath::handle($folderPath, $input['name'], $baseFolder, $input['force']);
        $namespace = ObtainNamespace::handle($folderPath, $baseFolder);
        $relativePath = dirname("{$baseFolder}/{$folderPath}/{$input['name']}.php");

        return new ActionConfig(
            name: $input['name'],
            methodName: $methodName,
            path: $path,
            namespace: $namespace,
            relativePath: $relativePath,
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
        $requestName = GenerateRequest::handle($config->name, $config->force);
        $this->info("Request {$requestName} created successfully...");
    }

    private function generateActionFile(ActionConfig $config): void
    {
        $stub = PrepareStub::handle(
            class: $config->name,
            namespace: $config->namespace,
            methodName: $config->methodName,
            transaction: $config->transaction,
            user: $config->user,
            request: $config->request,
            static: $config->static,
        );

        if (File::put($config->path, $stub) === false) {
            throw new RuntimeException("Failed to write the action file at {$config->path}. Check the directory permissions.");
        }
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
        $this->info("Action {$config->name} created successfully at app/{$config->relativePath} folder{$featuresText}");
    }
}
