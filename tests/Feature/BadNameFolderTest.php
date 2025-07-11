<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class BadNameFolderTest extends TestCase
{
    public function test_the_console_command_dont_create_a_action_with_bad_name_folder(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Bad', 'subfolder' => 'Bad*/Name'])
            ->assertExitCode(1);
        $this->assertFileDoesNotExist(app_path('Actions/Bad*/Name/Bad.php'));
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelActionServiceProvider::class];
    }
}
