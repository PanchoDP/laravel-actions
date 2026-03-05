
<p align="center"><a target="_blank"> <img alt="Logo for Laravel Action" src="art/laravel-action.webp"></a></p>

<p align="center">
<a ><img src="https://img.shields.io/badge/PHP-8.3%2B-blue" alt="Php"></a>
<a ><img src="https://img.shields.io/packagist/dt/panchodp/laravel-actions?" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/panchodp/laravel-actions"><img src="https://img.shields.io/packagist/v/panchodp/laravel-actions.svg?" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/panchodp/laravel-actions"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
<a href="https://github.com/PanchoDP/laravel-actions/actions/workflows/tests.yml"><img src="https://github.com/PanchoDP/laravel-actions/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
</p>



# Laravel Actions

Make your Laravel actions classes fast and in a simple way.

## Installation

You can install the package via composer:

```bash
composer require panchodp/laravel-actions --dev
```

## Configuration

Use the following command to publish the configuration file:
```bash
php artisan vendor:publish --provider="Panchodp\LaravelAction\LaravelActionServiceProvider" --tag="laravel-actions-config"
```
This will create a `config/laravel-actions.php` file in your application.

```php

return [
    'base_folder' => 'Actions',
    'method_name' => 'handle',
];
```
There are two configuration options available:
- `base_folder`: Base folder where action classes are created. Defaults to `Actions` (`app/Actions`).
- `method_name`: Method name generated in action classes. Defaults to `handle`.

## Method Types

By default, Laravel Actions generates **instance methods** for better flexibility and dependency injection support. However, you can create **static methods** when needed for simpler usage.

### Instance Methods (Default)
```php
// Usage
$action = new MyAction();
$action->handle($attributes);

// Generated code
public function handle(array $attributes): void
{
    // Implementation
}
```

### Static Methods
```php
// Usage
MyAction::handle($attributes);

// Generated code
public static function handle(array $attributes): void
{
    // Implementation
}
```

## Usage

### Creating Actions

To create an action class, use the `make:action` command. You can specify the full path using forward slashes `/` or backslashes `\` (Laravel-style syntax):

**Basic action:**
```bash
php artisan make:action MyAction
```
This creates a new action class in the `app/Actions` directory.

**Action in a subfolder:**
```bash
php artisan make:action User/CreateAccount
```
This creates the action in `app/Actions/User/CreateAccount.php`.

**Action in nested subfolders:**
```bash
php artisan make:action User/Auth/Login
```
This creates the action in `app/Actions/User/Auth/Login.php`.

**Using backslashes (alternative syntax):**
```bash
php artisan make:action Admin\DeletePost
```
This creates the action in `app/Actions/Admin/DeletePost.php`.

The generated class will have a `handle` method where you can implement your action logic:
```php
<?php

declare(strict_types=1);

namespace App\Actions\User;

use Throwable;

final class CreateAccount
{
    public function handle(array $attributes): void
    {
        // This is where the action logic will be implemented.
    }
}
```




### Flags

| Flag | Description |
|------|-------------|
| `--t` | Wraps the action body in a `DB::transaction` |
| `--u` | Injects `User $user` into the method |
| `--r` | Generates a `Request` class and injects it into the method |
| `--s` | Generates a `static` method instead of an instance method |

Flags can be combined in any order:

```bash
php artisan make:action MyAction --tur   # transaction + user + request
php artisan make:action MyAction --turs  # + static method
```

Example output for `--turs`:

```php
final class MyAction
{
    public static function handle(User $user, MyActionRequest $request): void
    {
        DB::transaction(function () use ($request) {
            // Logic to be executed within the transaction
        });
    }
}
```

## Other Userfull Commands:

You can show the Actions directory tree in the terminal with the following command:

```bash
php artisan actions:list
```
This will display the structure of the `app/Actions` or the base directory specified in the config file, showing all action classes and their subdirectories.

```bash
Actions/
├── Folder1/
│   ├── SecondAction
│   └── ThirdAction
├── Folder2/
│   └── FourthAction
├── FirstAction
└── LastAction
```



## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://github.com/PanchoDP/laravel-actions/blob/master/LICENSE.md)