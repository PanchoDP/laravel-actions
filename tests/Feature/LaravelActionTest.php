<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class LaravelActionTest extends TestCase
{
    public function test_the_console_command_create_a_simple_action(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public function handle(array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_simple_action(): void
    {
        File::deleteDirectory(app_path('Actions'));
        $this->assertFileDoesNotExist(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(1);
    }

    public function test_the_console_command_create_a_user_simple_action(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', '--u' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', '--u' => true, '--t' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions_option_tu(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_user_simple_action(): void
    {
        File::deleteDirectory(app_path('Actions'));
        $this->assertFileDoesNotExist(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', '--u' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(1);
    }

    public function test_the_console_command_create__simple_action_with_user_and_d_b_transactions_option_ut(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'use App\Models\User;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public function handle(User $user,array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'DB::transaction(function () use ($attributes)',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_the_console_command_dont_create_a_repeat_user_simple_action_option_tu(): void
    {
        File::deleteDirectory(app_path('Actions'));
        $this->assertFileDoesNotExist(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction', '--tu' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(1);
    }

    public function test_the_console_command_create_a_static_simple_action(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'SimpleAction', '--s' => true])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->assertStringContainsString(
            'namespace App\Actions;',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );

        $this->assertStringContainsString(
            'public static function handle(array $attributes): void',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionServiceProvider::class,
        ];
    }
}
