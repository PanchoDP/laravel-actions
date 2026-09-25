<?php

declare(strict_types=1);

use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;
use Panchodp\LaravelAction\Actions\ValidateFolder;

test('subfolder validation rejects path traversal and absolute paths', function (string $subfolder): void {
    expect(fn () => ValidateFolder::handle(PrepareSubfolder::handle($subfolder)))
        ->toThrow(InvalidArgumentException::class, 'Invalid folder name');
})->with(['../', '..\\', 'User/../../config', './User', 'C:\\Windows', 'C:/Windows']);

test('make:action never writes outside the actions folder', function (string $subfolder): void {
    $this->artisan('make:action', ['name' => 'Evil', 'subfolder' => $subfolder])
        ->assertExitCode(1);

    expect(base_path('Evil.php'))->not->toBeFile()
        ->and(app_path('Evil.php'))->not->toBeFile()
        ->and(app_path('Actions'))->not->toBeDirectory();
})->with(['../..', '..\\..', 'User/..', 'C:\\Windows']);

test('PrepareSubfolder allows safe paths', function (): void {
    // Test valid subfolders
    expect(PrepareSubfolder::handle('User/Auth'))->toBe(['User', 'Auth']);
    expect(PrepareSubfolder::handle('Admin'))->toBe(['Admin']);
    expect(PrepareSubfolder::handle(''))->toBe([]);
    expect(PrepareSubfolder::handle('User\\Auth'))->toBe(['User', 'Auth']);
    expect(PrepareSubfolder::handle(' User / Auth '))->toBe(['User', 'Auth']);
});

test('ValidateConfiguration prevents dangerous base folder names', function (): void {
    // Test path traversal in base folder
    expect(fn (): array => ValidateConfiguration::handle('../Actions', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: must start with a letter');

    expect(fn (): array => ValidateConfiguration::handle('/etc', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: must start with a letter');

    // Test invalid folder name format
    expect(fn (): array => ValidateConfiguration::handle('123Actions', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: must start with a letter');
});

test('ValidateConfiguration rejects magic method names', function (string $methodName): void {
    expect(fn (): array => ValidateConfiguration::handle('Actions', $methodName))
        ->toThrow(InvalidArgumentException::class, "Method name '{$methodName}' is not allowed: names starting with '__' are reserved for PHP magic methods.");
})->with(['__construct', '__invoke', '__callStatic', '__get', '__custom']);

test('ValidateConfiguration allows regular method names', function (string $methodName): void {
    expect(ValidateConfiguration::handle('Actions', $methodName)['method_name'])->toBe($methodName);
})->with(['handle', 'execute', 'run', '_private', 'exec']);

test('ValidateConfiguration rejects invalid method name formats', function (): void {
    expect(fn (): array => ValidateConfiguration::handle('Actions', '123method'))
        ->toThrow(InvalidArgumentException::class, 'Invalid method name: must be a valid PHP method name.');
});

test('ValidateConfiguration allows safe configuration values', function (): void {
    $result = ValidateConfiguration::handle('MyActions', 'execute');
    expect($result)->toBe([
        'base_folder' => 'MyActions',
        'method_name' => 'execute',
    ]);

    // Test default values
    $result = ValidateConfiguration::handle(null, null);
    expect($result)->toBe([
        'base_folder' => 'Actions',
        'method_name' => 'handle',
    ]);
});

test('PrepareStub generates a valid stub from safe inputs', function (): void {
    $stub = PrepareStub::handle('TestAction', 'App\\Actions');

    expect($stub)
        ->toContain('namespace App\\Actions;')
        ->toContain('final class TestAction')
        ->toContain('public function handle(');
});

test('CreateDirectory creates the directory', function (): void {
    $tempDir = sys_get_temp_dir().'/test_actions_security_'.uniqid();
    $testFile = $tempDir.'/TestAction.php';

    CreateDirectory::handle($testFile);

    expect(is_dir($tempDir))->toBeTrue();

    // Clean up
    rmdir($tempDir);
});

test('subfolders with spaces inside a folder name are rejected', function (): void {
    $this->artisan('make:action', ['name' => 'CreateUser', 'subfolder' => 'User Auth'])
        ->expectsOutputToContain('Invalid folder name: User Auth.')
        ->assertExitCode(1);

    expect(app_path('Actions/User/Auth/CreateUser.php'))->not->toBeFile();
});

test('configured values fall back to the defaults when missing or not strings', function (mixed $value): void {
    config(['laravel-actions.base_folder' => $value, 'laravel-actions.method_name' => $value]);

    expect(ValidateConfiguration::configuredBaseFolder())->toBe(ValidateConfiguration::DEFAULT_BASE_FOLDER)
        ->and(ValidateConfiguration::configuredMethodName())->toBe(ValidateConfiguration::DEFAULT_METHOD_NAME);
})->with([null, '', 123, false]);
