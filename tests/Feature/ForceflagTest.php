<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Panchodp\LaravelAction\Test\TestCase;

final class ForceFlagTest extends TestCase
{
    public function test_force_flag_overwrites_existing_action(): void
    {
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        // Modify the file to detect overwrite
        file_put_contents(app_path('Actions/SimpleAction.php'), '<?php // original content');

        $this->artisan('make:action', ['name' => 'SimpleAction', '--force' => true])
            ->assertExitCode(0);

        $this->assertStringContainsString(
            'class SimpleAction',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
        $this->assertStringNotContainsString(
            '// original content',
            file_get_contents(app_path('Actions/SimpleAction.php'))
        );
    }

    public function test_without_force_flag_fails_when_action_already_exists(): void
    {
        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(0);
        $this->assertFileExists(app_path('Actions/SimpleAction.php'));

        $this->artisan('make:action', ['name' => 'SimpleAction'])
            ->assertExitCode(1);
    }
}
