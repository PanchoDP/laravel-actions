<?php

declare(strict_types=1);

use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;

test('PrepareSubfolder prevents path traversal attacks', function (): void {
    // Test basic path traversal
    expect(fn (): array => PrepareSubfolder::handle('../'))
        ->toThrow(InvalidArgumentException::class, 'Invalid subfolder path: path traversal sequences are not allowed.');

    // Test Windows path traversal
    expect(fn (): array => PrepareSubfolder::handle('..\\'))
        ->toThrow(InvalidArgumentException::class);

    // Test URL encoded path traversal
    expect(fn (): array => PrepareSubfolder::handle('..%2f'))
        ->toThrow(InvalidArgumentException::class);

    // Test double URL encoded
    expect(fn (): array => PrepareSubfolder::handle('..%252f'))
        ->toThrow(InvalidArgumentException::class);

    // Test absolute paths
    expect(fn (): array => PrepareSubfolder::handle('/etc/passwd'))
        ->toThrow(InvalidArgumentException::class);

    expect(fn (): array => PrepareSubfolder::handle('C:\\Windows'))
        ->toThrow(InvalidArgumentException::class);
});

test('PrepareSubfolder allows safe paths', function (): void {
    // Test valid subfolders
    expect(PrepareSubfolder::handle('User/Auth'))->toBe(['User', 'Auth']);
    expect(PrepareSubfolder::handle('Admin'))->toBe(['Admin']);
    expect(PrepareSubfolder::handle(''))->toBe([]);
});

test('ValidateConfiguration prevents dangerous base folder names', function (): void {
    // Test path traversal in base folder
    expect(fn (): array => ValidateConfiguration::handle('../Actions', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: path traversal sequences are not allowed.');

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
    $stub = PrepareStub::handle(false, false, false, false, 'TestAction', 'App\\Actions');

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
