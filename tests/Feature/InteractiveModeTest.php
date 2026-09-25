<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Panchodp\LaravelAction\Test\TestCase;

final class InteractiveModeTest extends TestCase
{
    public function test_fails_with_clear_message_when_no_name_in_non_interactive_environment(): void
    {
        $this->artisan('make:action', ['--no-interaction' => true])
            ->expectsOutputToContain('Action name is required.')
            ->assertExitCode(1);
    }

    public function test_interactive_name_supports_path_syntax(): void
    {
        $this->artisan('make:action')
            ->expectsQuestion('Action name', 'User/Auth/CreateUser')
            ->expectsQuestion('Subfolder (optional)', '')
            ->expectsConfirmation('Include DB transaction?', 'no')
            ->expectsConfirmation('Inject User?', 'no')
            ->expectsConfirmation('Generate Request class?', 'no')
            ->expectsConfirmation('Static method?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/User/Auth/CreateUser.php'));
        $this->assertStringContainsString(
            'namespace App\Actions\User\Auth;',
            (string) file_get_contents(app_path('Actions/User/Auth/CreateUser.php')),
        );
    }

    public function test_interactive_mode_does_not_ask_for_flags_passed_on_the_command_line(): void
    {
        $this->artisan('make:action', ['--transaction' => true, '--static' => true])
            ->expectsQuestion('Action name', 'CreateInvoice')
            ->expectsQuestion('Subfolder (optional)', 'Billing')
            ->doesntExpectOutputToContain('Include DB transaction?')
            ->expectsConfirmation('Inject User?', 'no')
            ->expectsConfirmation('Generate Request class?', 'no')
            ->doesntExpectOutputToContain('Static method?')
            ->assertExitCode(0);

        $content = (string) file_get_contents(app_path('Actions/Billing/CreateInvoice.php'));
        $this->assertStringContainsString('DB::transaction', $content);
        $this->assertStringContainsString('public static function handle', $content);
    }

    public function test_interactive_mode_respects_force_flag(): void
    {
        $this->artisan('make:action', ['name' => 'Existing'])->assertExitCode(0);

        $this->artisan('make:action', ['--force' => true])
            ->expectsQuestion('Action name', 'Existing')
            ->expectsQuestion('Subfolder (optional)', '')
            ->expectsConfirmation('Include DB transaction?', 'yes')
            ->expectsConfirmation('Inject User?', 'no')
            ->expectsConfirmation('Generate Request class?', 'no')
            ->expectsConfirmation('Static method?', 'no')
            ->assertExitCode(0);

        $this->assertStringContainsString(
            'DB::transaction',
            (string) file_get_contents(app_path('Actions/Existing.php')),
        );
    }
}
