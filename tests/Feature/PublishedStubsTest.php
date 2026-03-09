<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Panchodp\LaravelAction\Test\TestCase;

final class PublishedStubsTest extends TestCase
{
    private string $publishedStubsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->publishedStubsPath = resource_path('stubs/vendor/laravel-actions');
        File::deleteDirectory($this->publishedStubsPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publishedStubsPath);
        parent::tearDown();
    }

    public function test_uses_package_stub_when_no_published_stub_exists(): void
    {
        $this->artisan('make:action', ['name' => 'MyAction'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/MyAction.php'));
        $this->assertStringContainsString(
            'public function handle(array $attributes): void',
            file_get_contents(app_path('Actions/MyAction.php'))
        );
    }

    public function test_uses_published_stub_when_it_exists(): void
    {
        File::makeDirectory($this->publishedStubsPath, 0755, true);
        File::put($this->publishedStubsPath.'/action.stub', '<?php // custom stub for {{ class }}');

        $this->artisan('make:action', ['name' => 'MyAction'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/MyAction.php'));
        $this->assertStringContainsString(
            '// custom stub for MyAction',
            file_get_contents(app_path('Actions/MyAction.php'))
        );
    }

    public function test_falls_back_to_package_stub_when_published_stub_does_not_exist_for_that_variant(): void
    {
        // Publish only the basic stub, not the transaction one
        File::makeDirectory($this->publishedStubsPath, 0755, true);
        File::put($this->publishedStubsPath.'/action.stub', '<?php // custom stub for {{ class }}');

        $this->artisan('make:action', ['name' => 'MyAction', '--transaction' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/MyAction.php'));
        $this->assertStringContainsString(
            'DB::transaction',
            file_get_contents(app_path('Actions/MyAction.php'))
        );
    }
}
