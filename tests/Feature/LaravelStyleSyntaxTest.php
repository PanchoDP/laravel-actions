<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class LaravelStyleSyntaxTest extends TestCase
{
    public function test_laravel_style_syntax_with_forward_slash(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Top/Topisima'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Top/Topisima.php'));

        $content = file_get_contents(app_path('Actions/Top/Topisima.php'));
        $this->assertStringContainsString('namespace App\Actions\Top;', $content);
        $this->assertStringContainsString('class Topisima', $content);
    }

    public function test_laravel_style_syntax_with_backward_slash(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Admin\CreateUser'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Admin/CreateUser.php'));

        $content = file_get_contents(app_path('Actions/Admin/CreateUser.php'));
        $this->assertStringContainsString('namespace App\Actions\Admin;', $content);
        $this->assertStringContainsString('class CreateUser', $content);
    }

    public function test_laravel_style_syntax_with_nested_path(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'User/Auth/Login'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/User/Auth/Login.php'));

        $content = file_get_contents(app_path('Actions/User/Auth/Login.php'));
        $this->assertStringContainsString('namespace App\Actions\User\Auth;', $content);
        $this->assertStringContainsString('class Login', $content);
    }

    public function test_backward_compatibility_with_original_syntax(): void
    {
        File::deleteDirectory(app_path('Actions'));

        // Original syntax: name as first arg, subfolder as second arg
        $this->artisan('make:action', ['name' => 'Topisima', 'subfolder' => 'Top'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Top/Topisima.php'));

        $content = file_get_contents(app_path('Actions/Top/Topisima.php'));
        $this->assertStringContainsString('namespace App\Actions\Top;', $content);
        $this->assertStringContainsString('class Topisima', $content);
    }

    public function test_combined_syntax_with_both_path_and_subfolder(): void
    {
        File::deleteDirectory(app_path('Actions'));

        // Using both: path in name AND subfolder argument
        // This should combine them: User/Auth + Extra = User/Auth/Extra
        $this->artisan('make:action', ['name' => 'User/Auth/Login', 'subfolder' => 'Extra'])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/User/Auth/Extra/Login.php'));

        $content = file_get_contents(app_path('Actions/User/Auth/Extra/Login.php'));
        $this->assertStringContainsString('namespace App\Actions\User\Auth\Extra;', $content);
        $this->assertStringContainsString('class Login', $content);
    }

    public function test_laravel_style_syntax_with_transaction_flag(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Admin/CreatePost', '--transaction' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Admin/CreatePost.php'));

        $content = file_get_contents(app_path('Actions/Admin/CreatePost.php'));
        $this->assertStringContainsString('namespace App\Actions\Admin;', $content);
        $this->assertStringContainsString('class CreatePost', $content);
        $this->assertStringContainsString('DB::transaction', $content);
    }

    public function test_laravel_style_syntax_with_user_flag(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'User/UpdateProfile', '--user' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/User/UpdateProfile.php'));

        $content = file_get_contents(app_path('Actions/User/UpdateProfile.php'));
        $this->assertStringContainsString('namespace App\Actions\User;', $content);
        $this->assertStringContainsString('class UpdateProfile', $content);
        $this->assertStringContainsString('use App\Models\User;', $content);
        $this->assertStringContainsString('public function handle(User $user,array $attributes): void', $content);
    }

    public function test_laravel_style_syntax_with_request_flag(): void
    {
        File::deleteDirectory(app_path('Actions'));
        File::deleteDirectory(app_path('Http/Requests'));

        $this->artisan('make:action', ['name' => 'Post/Create', '--request' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Post/Create.php'));

        $content = file_get_contents(app_path('Actions/Post/Create.php'));
        $this->assertStringContainsString('namespace App\Actions\Post;', $content);
        $this->assertStringContainsString('class Create', $content);
        $this->assertStringContainsString('use App\Http\Requests\CreateRequest;', $content);
    }

    public function test_laravel_style_syntax_with_multiple_flags(): void
    {
        File::deleteDirectory(app_path('Actions'));
        File::deleteDirectory(app_path('Http/Requests'));

        $this->artisan('make:action', ['name' => 'Order/Process', '--transaction' => true, '--user' => true, '--request' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Order/Process.php'));

        $content = file_get_contents(app_path('Actions/Order/Process.php'));
        $this->assertStringContainsString('namespace App\Actions\Order;', $content);
        $this->assertStringContainsString('class Process', $content);
        $this->assertStringContainsString('use App\Models\User;', $content);
        $this->assertStringContainsString('use App\Http\Requests\ProcessRequest;', $content);
        $this->assertStringContainsString('DB::transaction', $content);
    }

    public function test_laravel_style_syntax_with_static_flag(): void
    {
        File::deleteDirectory(app_path('Actions'));

        $this->artisan('make:action', ['name' => 'Util/FormatDate', '--static' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Actions/Util/FormatDate.php'));

        $content = file_get_contents(app_path('Actions/Util/FormatDate.php'));
        $this->assertStringContainsString('namespace App\Actions\Util;', $content);
        $this->assertStringContainsString('class FormatDate', $content);
        $this->assertStringContainsString('public static function handle(array $attributes): void', $content);
    }

    public function test_security_path_traversal_still_blocked_with_new_syntax(): void
    {
        File::deleteDirectory(app_path('Actions'));

        // Path traversal should still be blocked
        $this->artisan('make:action', ['name' => '../Evil/Hack'])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(app_path('Actions/Evil/Hack.php'));
    }

    public function test_invalid_class_name_in_path_still_fails(): void
    {
        File::deleteDirectory(app_path('Actions'));

        // Invalid class name should still fail validation
        $this->artisan('make:action', ['name' => 'User/Bad*Name'])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(app_path('Actions/User/Bad*Name.php'));
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionServiceProvider::class,
        ];
    }
}
