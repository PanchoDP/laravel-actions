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

test('ValidateConfiguration prevents dangerous method names', function (): void {
    // Test dangerous method names
    expect(fn (): array => ValidateConfiguration::handle('Actions', '__construct'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'__construct\' is not allowed for security reasons.');

    expect(fn (): array => ValidateConfiguration::handle('Actions', 'eval'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'eval\' is not allowed for security reasons.');

    expect(fn (): array => ValidateConfiguration::handle('Actions', 'exec'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'exec\' is not allowed for security reasons.');

    // Test invalid method name format
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
