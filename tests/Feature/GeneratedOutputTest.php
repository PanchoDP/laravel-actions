<?php

declare(strict_types=1);

use Panchodp\LaravelAction\Actions\PrepareStub;

test('generated file has no consecutive blank lines and a single trailing newline', function (): void {
    $this->artisan('make:action', ['name' => 'CleanAction'])->assertExitCode(0);

    $content = file_get_contents(app_path('Actions/CleanAction.php'));

    expect($content)
        ->not->toMatch('/\n{3,}/')
        ->toEndWith("}\n")
        ->toContain("namespace App\\Actions;\n\nfinal class CleanAction");
});

test('generated file with user import keeps a single blank line around the import', function (): void {
    $this->artisan('make:action', ['name' => 'ImportAction', '--user' => true])->assertExitCode(0);

    $content = file_get_contents(app_path('Actions/ImportAction.php'));

    expect($content)
        ->not->toMatch('/\n{3,}/')
        ->toEndWith("}\n")
        ->toContain("use App\\Models\\User;\n\nfinal class ImportAction")
        ->toContain('public function handle(User $user, array $attributes): void');
});

test('published stubs are normalized too', function (): void {
    $stub = PrepareStub::handle(false, false, false, false, 'TestAction', 'App\\Actions');

    expect($stub)
        ->not->toMatch('/\n{3,}/')
        ->toEndWith("\n")
        ->not->toEndWith("\n\n");
});