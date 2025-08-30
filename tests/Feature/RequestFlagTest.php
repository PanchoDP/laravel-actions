<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Test\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Panchodp\LaravelAction\LaravelActionServiceProvider;

final class RequestFlagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(app_path('Actions'));
        File::deleteDirectory(app_path('Http/Requests'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(app_path('Actions'));
        File::deleteDirectory(app_path('Http/Requests'));
        parent::tearDown();
    }

    public function test_console_command_creates_action_with_request_flag(): void
    {
        $this->artisan('make:action', [
            'name' => 'LimpiarAction',
            '--r' => true,
        ])->assertExitCode(0);

        // Assert Action file exists
        $this->assertFileExists(app_path('Actions/LimpiarAction.php'));

        // Assert Request file exists
        $this->assertFileExists(app_path('Http/Requests/LimpiarActionRequest.php'));

        // Assert Action contains Request import
        $actionContent = file_get_contents(app_path('Actions/LimpiarAction.php'));
        $this->assertStringContainsString(
            'use App\Http\Requests\LimpiarActionRequest;',
            $actionContent
        );

        // Assert Action method has Request parameter
        $this->assertStringContainsString(
            'LimpiarActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_transaction_and_request_flags(): void
    {
        $this->artisan('make:action', [
            'name' => 'TransactionAction',
            '--t' => true,
            '--r' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/TransactionAction.php'));
        $this->assertFileExists(app_path('Http/Requests/TransactionActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/TransactionAction.php'));

        // Assert contains DB transaction
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\DB;',
            $actionContent
        );
        $this->assertStringContainsString(
            'DB::transaction(function () use ($request)',
            $actionContent
        );

        // Assert contains Request import and parameter
        $this->assertStringContainsString(
            'use App\Http\Requests\TransactionActionRequest;',
            $actionContent
        );
        $this->assertStringContainsString(
            'TransactionActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_user_and_request_flags(): void
    {
        $this->artisan('make:action', [
            'name' => 'UserAction',
            '--u' => true,
            '--r' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/UserAction.php'));
        $this->assertFileExists(app_path('Http/Requests/UserActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/UserAction.php'));

        // Assert contains User import
        $this->assertStringContainsString(
            'use App\Models\User;',
            $actionContent
        );

        // Assert contains Request import
        $this->assertStringContainsString(
            'use App\Http\Requests\UserActionRequest;',
            $actionContent
        );

        // Assert method has both User and Request parameters
        $this->assertStringContainsString(
            'User $user,UserActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_all_flags(): void
    {
        $this->artisan('make:action', [
            'name' => 'CompleteAction',
            '--t' => true,
            '--u' => true,
            '--r' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/CompleteAction.php'));
        $this->assertFileExists(app_path('Http/Requests/CompleteActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/CompleteAction.php'));

        // Assert contains all imports
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\DB;',
            $actionContent
        );
        $this->assertStringContainsString(
            'use App\Models\User;',
            $actionContent
        );
        $this->assertStringContainsString(
            'use App\Http\Requests\CompleteActionRequest;',
            $actionContent
        );

        // Assert method has all parameters and transaction
        $this->assertStringContainsString(
            'User $user,CompleteActionRequest $request',
            $actionContent
        );
        $this->assertStringContainsString(
            'DB::transaction(function () use ($request)',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_request_in_subfolder(): void
    {
        $this->artisan('make:action', [
            'name' => 'SubfolderAction',
            'subfolder' => 'Admin',
            '--r' => true,
        ])->assertExitCode(0);

        // Assert Action file exists in subfolder
        $this->assertFileExists(app_path('Actions/Admin/SubfolderAction.php'));

        // Assert Request file exists
        $this->assertFileExists(app_path('Http/Requests/SubfolderActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/Admin/SubfolderAction.php'));

        // Assert contains correct namespace
        $this->assertStringContainsString(
            'namespace App\Actions\Admin;',
            $actionContent
        );

        // Assert contains Request import and parameter
        $this->assertStringContainsString(
            'use App\Http\Requests\SubfolderActionRequest;',
            $actionContent
        );
        $this->assertStringContainsString(
            'SubfolderActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_tr_flag(): void
    {
        $this->artisan('make:action', [
            'name' => 'TRAction',
            '--tr' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/TRAction.php'));
        $this->assertFileExists(app_path('Http/Requests/TRActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/TRAction.php'));

        // Assert contains DB transaction
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\DB;',
            $actionContent
        );
        $this->assertStringContainsString(
            'DB::transaction(function () use ($request)',
            $actionContent
        );

        // Assert contains Request import and parameter
        $this->assertStringContainsString(
            'use App\Http\Requests\TRActionRequest;',
            $actionContent
        );
        $this->assertStringContainsString(
            'TRActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_ur_flag(): void
    {
        $this->artisan('make:action', [
            'name' => 'URAction',
            '--ur' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/URAction.php'));
        $this->assertFileExists(app_path('Http/Requests/URActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/URAction.php'));

        // Assert contains User import
        $this->assertStringContainsString(
            'use App\Models\User;',
            $actionContent
        );

        // Assert contains Request import
        $this->assertStringContainsString(
            'use App\Http\Requests\URActionRequest;',
            $actionContent
        );

        // Assert method has both User and Request parameters
        $this->assertStringContainsString(
            'User $user,URActionRequest $request',
            $actionContent
        );
    }

    public function test_console_command_creates_action_with_tur_flag(): void
    {
        $this->artisan('make:action', [
            'name' => 'TURAction',
            '--tur' => true,
        ])->assertExitCode(0);

        // Assert both files exist
        $this->assertFileExists(app_path('Actions/TURAction.php'));
        $this->assertFileExists(app_path('Http/Requests/TURActionRequest.php'));

        $actionContent = file_get_contents(app_path('Actions/TURAction.php'));

        // Assert contains all imports
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\DB;',
            $actionContent
        );
        $this->assertStringContainsString(
            'use App\Models\User;',
            $actionContent
        );
        $this->assertStringContainsString(
            'use App\Http\Requests\TURActionRequest;',
            $actionContent
        );

        // Assert method has all parameters and transaction
        $this->assertStringContainsString(
            'User $user,TURActionRequest $request',
            $actionContent
        );
        $this->assertStringContainsString(
            'DB::transaction(function () use ($request)',
            $actionContent
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionServiceProvider::class,
        ];
    }
}
