<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class BadNameTest extends TestCase
{
    public function test_the_console_command_dont_create_a_action_with_bad_name(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Bad*/Name'])
            ->assertExitCode(1);
        $this->assertFileDoesNotExist(app_path('Actions/Bad Name.php'));
    }

    public function test_the_console_command_dont_create_a_action_without_name(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => ''])
            ->assertExitCode(1);
        $this->assertFileDoesNotExist(app_path('Actions/Bad Name.php'));
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelActionServiceProvider::class];
    }
}
