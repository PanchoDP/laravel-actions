<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('displays empty actions directory tree when no actions exist', function () {
    $actionsPath = app_path('Actions');

    // Ensure directory doesn't exist
    if (File::exists($actionsPath)) {
        File::deleteDirectory($actionsPath);
    }

    Artisan::call('list:actions');
    $output = Artisan::output();

    expect($output)->toContain('Actions directory not found');
});

it('displays actions directory tree with sample actions', function () {
    $actionsPath = app_path('Actions');

    // Create test directory structure
    File::makeDirectory($actionsPath, 0755, true);
    File::makeDirectory($actionsPath.'/User', 0755, true);
    File::makeDirectory($actionsPath.'/Product', 0755, true);

    // Create sample action files
    File::put($actionsPath.'/CreateOrderAction.php', '<?php class CreateOrderAction { public function handle() {} }');
    File::put($actionsPath.'/User/UpdateUserAction.php', '<?php class UpdateUserAction { public function handle() {} }');
    File::put($actionsPath.'/Product/DeleteProductAction.php', '<?php class DeleteProductAction { public function handle() {} }');

    // Create a non-PHP file that should be filtered out
    File::put($actionsPath.'/readme.txt', 'This is a readme file');

    Artisan::call('list:actions');
    $output = Artisan::output();

    expect($output)->toContain('Actions Directory Tree:');
    expect($output)->toContain('Actions/');
    expect($output)->toContain('CreateOrderAction');
    expect($output)->toContain('├── Product/');
    expect($output)->toContain('UpdateUserAction');
    expect($output)->toContain('├── User/');
    expect($output)->toContain('DeleteProductAction');
    expect($output)->not->toContain('readme.txt');

    // Clean up
    File::deleteDirectory($actionsPath);
});

it('properly filters non-action php files', function () {
    $actionsPath = app_path('Actions');

    // Create test directory
    File::makeDirectory($actionsPath, 0755, true);

    // Create an action file
    File::put($actionsPath.'/ValidAction.php', '<?php class ValidAction { public function handle() {} }');

    // Create files that shouldn't be included (non-Action file)
    File::put($actionsPath.'/readme.txt', 'This is a readme file');
    File::put($actionsPath.'/config.json', '{"setting": "value"}');

    Artisan::call('list:actions');
    $output = Artisan::output();

    expect($output)->toContain('ValidAction');
    expect($output)->not->toContain('NotAnAction');
    expect($output)->not->toContain('readme.txt');

    // Clean up
    File::deleteDirectory($actionsPath);
});
