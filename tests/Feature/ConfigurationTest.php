<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class ConfigurationTest extends TestCase
{
    public function test_the_console_command_dont_create_a_action_with_bad_name(): void
    {
        File::deleteDirectory(app_path('Actions'));
        File::deleteDirectory(app_path('Prueba'));

        config(['laravel-actions.base_folder' => 'Prueba']);

        $this->artisan('make:action', ['name' => 'Name'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Prueba/Name.php'));
        $this->assertFileDoesNotExist(app_path('Actions/Name.php'));
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelActionServiceProvider::class];
    }
}
