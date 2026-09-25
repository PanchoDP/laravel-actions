<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\Actions\ValidateFolder;
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

    public function test_invalid_folder_message_is_a_single_line(): void
    {
        try {
            ValidateFolder::handle(['User', 'Bad*']);
            $this->fail('Expected an InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame(
                'Invalid folder name: Bad*. Folder names must start with a letter or underscore and can only contain letters, numbers, and underscores.',
                $e->getMessage(),
            );
        }
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelActionServiceProvider::class];
    }
}
