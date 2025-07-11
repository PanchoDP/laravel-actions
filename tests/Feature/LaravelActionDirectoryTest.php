<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use File;
use Panchodp\LaravelAction\Test\TestCase;

final class LaravelActionDirectoryTest extends TestCase
{
    public function test_the_console_command_create_a_simple_action_in_a_directory(): void
    {

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions\Posts;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(array $attributes): void',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_simple_action_in_a_directory(): void
    {

        $this->assertFileDoesNotExist(app_path('Actions/Posts/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts'])
            ->assertExitCode(1);
    }

    public function test_the_console_command_create_a_user_simple_action_in_a_directory(): void
    {

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--u' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions\Posts;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions_in_a_directory(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--u' => true, '--t' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions\Posts;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions_option_tu_in_a_directory(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions\Posts;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_user_simple_action_in_a_directory(): void
    {
        File::deleteDirectory(app_path('Actions'));
        $this->assertFileDoesNotExist(app_path('Actions/Posts/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--u' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts'])
            ->assertExitCode(1);
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions_option_u_in_a_directory(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions\Posts;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/Posts/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_user_simple_action_option_tu_in_a_directory(): void
    {
        File::deleteDirectory(app_path('Actions'));
        @unlink(app_path('Actions/SimpleAction.php'));
        $this->assertFileDoesNotExist(app_path('Actions/SimpleAction.php'));

        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/Posts/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', 'subfolder' => 'Posts'])
            ->assertExitCode(1);
    }
}
