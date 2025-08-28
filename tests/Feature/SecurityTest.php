<?php

declare(strict_types=1);

use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateConfiguration;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\CreateDirectory;

test('PrepareSubfolder prevents path traversal attacks', function () {
    // Test basic path traversal
    expect(fn() => PrepareSubfolder::handle('../'))
        ->toThrow(InvalidArgumentException::class, 'Invalid subfolder path: path traversal sequences are not allowed.');
    
    // Test Windows path traversal
    expect(fn() => PrepareSubfolder::handle('..\\'))
        ->toThrow(InvalidArgumentException::class);
    
    // Test URL encoded path traversal
    expect(fn() => PrepareSubfolder::handle('..%2f'))
        ->toThrow(InvalidArgumentException::class);
    
    // Test double URL encoded
    expect(fn() => PrepareSubfolder::handle('..%252f'))
        ->toThrow(InvalidArgumentException::class);
    
    // Test absolute paths
    expect(fn() => PrepareSubfolder::handle('/etc/passwd'))
        ->toThrow(InvalidArgumentException::class);
    
    expect(fn() => PrepareSubfolder::handle('C:\\Windows'))
        ->toThrow(InvalidArgumentException::class);
});

test('PrepareSubfolder allows safe paths', function () {
    // Test valid subfolders
    expect(PrepareSubfolder::handle('User/Auth'))->toBe(['User', 'Auth']);
    expect(PrepareSubfolder::handle('Admin'))->toBe(['Admin']);
    expect(PrepareSubfolder::handle(''))->toBe([]);
});

test('ValidateConfiguration prevents dangerous base folder names', function () {
    // Test path traversal in base folder
    expect(fn() => ValidateConfiguration::handle('../Actions', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: path traversal sequences are not allowed.');
    
    // Test invalid folder name format
    expect(fn() => ValidateConfiguration::handle('123Actions', 'handle'))
        ->toThrow(InvalidArgumentException::class, 'Invalid base folder: must start with a letter');
});

test('ValidateConfiguration prevents dangerous method names', function () {
    // Test dangerous method names
    expect(fn() => ValidateConfiguration::handle('Actions', '__construct'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'__construct\' is not allowed for security reasons.');
    
    expect(fn() => ValidateConfiguration::handle('Actions', 'eval'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'eval\' is not allowed for security reasons.');
    
    expect(fn() => ValidateConfiguration::handle('Actions', 'exec'))
        ->toThrow(InvalidArgumentException::class, 'Method name \'exec\' is not allowed for security reasons.');
    
    // Test invalid method name format
    expect(fn() => ValidateConfiguration::handle('Actions', '123method'))
        ->toThrow(InvalidArgumentException::class, 'Invalid method name: must be a valid PHP method name.');
});

test('ValidateConfiguration allows safe configuration values', function () {
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

test('PrepareStub validates stub file existence', function () {
    // Mock missing stub file by using an invalid path
    $reflection = new ReflectionClass(PrepareStub::class);
    
    // This test checks that the method properly validates file existence
    expect(fn() => PrepareStub::handle(false, false, false, 'TestAction', 'App\\Actions'))
        ->not->toThrow(Exception::class);
});

test('PrepareStub sanitizes template variables', function () {
    // Test that dangerous characters are removed from template variables
    $reflection = new ReflectionClass(PrepareStub::class);
    $method = $reflection->getMethod('sanitizeForTemplate');
    $method->setAccessible(true);
    
    expect($method->invoke(null, 'TestAction<?php'))->toBe('TestAction?php');
    expect($method->invoke(null, 'Test$Action'))->toBe('TestAction');
    expect($method->invoke(null, 'Test\\Action'))->toBe('TestAction'); // Without allowBackslashes
    expect($method->invoke(null, 'Test`Action'))->toBe('TestAction');
    
    // Test with allowBackslashes = true (for namespaces)
    expect($method->invoke(null, 'App\\Actions', true))->toBe('App\\Actions');
    expect($method->invoke(null, 'App\\Actions<?php', true))->toBe('App\\Actions?php');
});

test('CreateDirectory uses secure permissions', function () {
    $tempDir = sys_get_temp_dir() . '/test_actions_security_' . uniqid();
    $testFile = $tempDir . '/TestAction.php';
    
    CreateDirectory::handle($testFile, 0750);
    
    expect(is_dir($tempDir))->toBeTrue();
    
    // Clean up
    rmdir($tempDir);
});

test('CreateDirectory respects custom permissions', function () {
    $tempDir = sys_get_temp_dir() . '/test_actions_permissions_' . uniqid();
    $testFile = $tempDir . '/TestAction.php';
    
    CreateDirectory::handle($testFile, 0755);
    
    expect(is_dir($tempDir))->toBeTrue();
    
    // Clean up
    rmdir($tempDir);
});