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
}
