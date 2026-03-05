<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
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

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

final class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name?} {subfolder?}
    {--t : Make a DB transaction action }
    {--u : Make a User injection}
    {--r : Generate a Request class and inject it into the action}
    {--s : Make a static method}
    {--tu : Make a DB transaction action with User injection}
    {--ut : Make a DB transaction action with User injection}
    {--tr : Make a DB transaction action with Request injection}
    {--rt : Make a DB transaction action with Request injection}
    {--ur : Make a User injection with Request injection}
    {--ru : Make a User injection with Request injection}
    {--ts : Make a DB transaction action with static method}
    {--st : Make a DB transaction action with static method}
    {--us : Make a User injection with static method}
    {--su : Make a User injection with static method}
    {--rs : Make a Request injection with static method}
    {--sr : Make a Request injection with static method}
    {--tur : Make a DB transaction action with User and Request injection}
    {--tru : Make a DB transaction action with User and Request injection}
    {--utr : Make a DB transaction action with User and Request injection}
    {--urt : Make a DB transaction action with User and Request injection}
    {--rtu : Make a DB transaction action with User and Request injection}
    {--rut : Make a DB transaction action with User and Request injection}
    {--tus : Make a DB transaction action with User injection and static method}
    {--tsu : Make a DB transaction action with User injection and static method}
    {--uts : Make a DB transaction action with User injection and static method}
    {--ust : Make a DB transaction action with User injection and static method}
    {--stu : Make a DB transaction action with User injection and static method}
    {--sut : Make a DB transaction action with User injection and static method}
    {--trs : Make a DB transaction action with Request injection and static method}
    {--tsr : Make a DB transaction action with Request injection and static method}
    {--rts : Make a DB transaction action with Request injection and static method}
    {--rst : Make a DB transaction action with Request injection and static method}
    {--str : Make a DB transaction action with Request injection and static method}
    {--srt : Make a DB transaction action with Request injection and static method}
    {--urs : Make a User injection with Request injection and static method}
    {--usr : Make a User injection with Request injection and static method}
    {--rus : Make a User injection with Request injection and static method}
    {--rsu : Make a User injection with Request injection and static method}
    {--sru : Make a User injection with Request injection and static method}
    {--sur : Make a User injection with Request injection and static method}
    {--turs : Make a DB transaction action with User, Request injection and static method}
    {--trsu : Make a DB transaction action with User, Request injection and static method}
    {--utrs : Make a DB transaction action with User, Request injection and static method}
    {--urts : Make a DB transaction action with User, Request injection and static method}
    {--rtus : Make a DB transaction action with User, Request injection and static method}
    {--ruts : Make a DB transaction action with User, Request injection and static method}
    {--stru : Make a DB transaction action with User, Request injection and static method}
    {--stur : Make a DB transaction action with User, Request injection and static method}
    {--sutr : Make a DB transaction action with User, Request injection and static method}
    {--surt : Make a DB transaction action with User, Request injection and static method}
    {--srtu : Make a DB transaction action with User, Request injection and static method}
    {--srut : Make a DB transaction action with User, Request injection and static method}
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
            $input = $this->processInputs();
            $config = $this->validateAndPrepareConfig($input);
            $this->createDirectoryStructure($config);

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

        if ($name === '') {
            if (! $this->input->isInteractive()) {
                throw new InvalidArgumentException('Action name is required.');
            }

            return $this->askInteractive();
        }

        $subfolder = $this->argument('subfolder');
        $subfolder = is_string($subfolder) ? mb_trim($subfolder, '/\\') : '';

        if (preg_match('#[/\\\\]#', $name)) {
            $parts = preg_split('#[/\\\\]+#', $name, -1, PREG_SPLIT_NO_EMPTY);

            if ($parts !== false && count($parts) > 1) {
                $className = array_pop($parts);
                $pathFromName = implode('/', $parts);

                $subfolder = $subfolder === '' || $subfolder === '0' ? $pathFromName : $pathFromName.'/'.$subfolder;

                $name = $className;
            }
        }

        $subfolder = mb_trim($subfolder, '/\\');

        $tursFlag = $this->option('turs') || $this->option('trsu') ||
                           $this->option('utrs') || $this->option('urts') ||
                           $this->option('rtus') || $this->option('ruts') ||
                           $this->option('stru') || $this->option('stur') ||
                           $this->option('sutr') || $this->option('surt') ||
                           $this->option('srtu') || $this->option('srut');

        $tusFlag = $this->option('tus') || $this->option('tsu') ||
                          $this->option('uts') || $this->option('ust') ||
                          $this->option('stu') || $this->option('sut') || $tursFlag;

        $trsFlag = $this->option('trs') || $this->option('tsr') ||
                          $this->option('rts') || $this->option('rst') ||
                          $this->option('str') || $this->option('srt') || $tursFlag;

        $ursFlag = $this->option('urs') || $this->option('usr') ||
                          $this->option('rus') || $this->option('rsu') ||
                          $this->option('sru') || $this->option('sur') || $tursFlag;

        $turFlag = $this->option('tur') || $this->option('tru') ||
                          $this->option('utr') || $this->option('urt') ||
                          $this->option('rtu') || $this->option('rut') || $tursFlag;

        $tsFlag = $this->option('ts') || $this->option('st') || $tusFlag || $trsFlag;
        $usFlag = $this->option('us') || $this->option('su') || $tusFlag || $ursFlag;
        $rsFlag = $this->option('rs') || $this->option('sr') || $trsFlag || $ursFlag;

        $tuFlag = $this->option('tu') || $this->option('ut') || $turFlag || $tusFlag;
        $trFlag = $this->option('tr') || $this->option('rt') || $turFlag || $trsFlag;
        $urFlag = $this->option('ur') || $this->option('ru') || $turFlag || $ursFlag;

        $tFlag = $this->option('t') || $tuFlag || $trFlag || $tsFlag;
        $uFlag = $this->option('u') || $tuFlag || $urFlag || $usFlag;
        $rFlag = $this->option('r') || $trFlag || $urFlag || $rsFlag;
        $sFlag = $this->option('s') || $tsFlag || $usFlag || $rsFlag;

        return [
            'name' => $name,
            'subfolder' => $subfolder,
            'tFlag' => $tFlag,
            'uFlag' => $uFlag,
            'rFlag' => $rFlag,
            'sFlag' => $sFlag,
            'force' => (bool) $this->option('force'),
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
        $force = is_bool($input['force']) && $input['force'];
        $path = PreparePath::handle($folder_path, $name, $validatedConfig['base_folder'], $force);
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
        $path = is_string($config['path']) ? $config['path'] : '';
        $relativePath = is_string($config['relative_path']) ? $config['relative_path'] : '';

        CreateDirectory::handle($path);
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
        $tFlag = is_bool($config['tFlag']) && $config['tFlag'];
        $uFlag = is_bool($config['uFlag']) && $config['uFlag'];
        $rFlag = is_bool($config['rFlag']) && $config['rFlag'];
        $sFlag = is_bool($config['sFlag']) && $config['sFlag'];
        $filename = is_string($config['filename']) ? $config['filename'] : '';
        $namespace = is_string($config['namespace']) ? $config['namespace'] : '';
        $path = is_string($config['path']) ? $config['path'] : '';

        $stub = PrepareStub::handle(
            $tFlag,
            $uFlag,
            $rFlag,
            $sFlag,
            $filename,
            $namespace
        );

        File::put($path, $stub);
    }

    /**
     * @return array<string, mixed>
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

        $tFlag = confirm(label: 'Include DB transaction?', default: false);
        $uFlag = confirm(label: 'Inject User?', default: false);
        $rFlag = confirm(label: 'Generate Request class?', default: false);
        $sFlag = confirm(label: 'Static method?', default: false);

        return [
            'name' => mb_trim($name),
            'subfolder' => mb_trim($subfolder, '/\\'),
            'tFlag' => $tFlag,
            'uFlag' => $uFlag,
            'rFlag' => $rFlag,
            'sFlag' => $sFlag,
            'force' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function displaySuccessMessages(array $config): void
    {
        $tFlag = is_bool($config['tFlag']) && $config['tFlag'];
        $uFlag = is_bool($config['uFlag']) && $config['uFlag'];
        $rFlag = is_bool($config['rFlag']) && $config['rFlag'];
        $sFlag = is_bool($config['sFlag']) && $config['sFlag'];
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
        if ($sFlag) {
            $features[] = 'static method';
        }

        $featuresText = $features === [] ? '.' : ' with '.implode(', ', $features).'.';
        $this->info("Action {$filename} created successfully at app/{$relativePath} folder{$featuresText}");
    }
}
