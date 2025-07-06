<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected string $tempAppPath;

    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(app_path('Actions'));
    }

    protected function tearDown(): void
    {

        parent::tearDown();
        File::deleteDirectory(app_path('Actions'));
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionServiceProvider::class,
        ];
    }
}
