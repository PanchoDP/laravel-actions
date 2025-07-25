  <picture>
    <img alt="Logo for Laravel Action" src="art/header-light.png">
  </picture>

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
- `base_folder`: This is the base folder where your action classes will be created. By default, it is set to `Actions`, which means your action classes will be created in the `app/Actions` directory.
- `method_name`: This is the name of the method that will be created in your action classes. By default, it is set to `handle`, which means your action classes will have a `handle` method where you can implement your action logic.

## Usage

1. To make an action class, you can use the `make:action` command:

```bash
php artisan make:action MyAction
```
This will create a new action class in the `app/Actions` directory with the name `MyAction.php`.

The class will have a `handle` method where you can implement your action logic.
```php
<?php

declare(strict_types=1);

namespace App\Actions;


use Throwable;

final class MyAction
{
            /**
             * Handle the action.
             *
             *
             * @param array $attributes
             * @return void
             * @throws Throwable
             */

    public static function handle(array $attributes): void
    {
        // This is where the action logic will be implemented.
    }
}
```


2. To make an action class in a specific subfolder of Action, you can use:

```bash
php artisan make:action MyAction Folder
```
This will create a new action class in the `app/Actions/Folder` directory with the name `MyAction.php`.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Folder;


use Throwable;

final class MyAction
{
            /**
             * Handle the action.
             *
             *
             * @param array $attributes
             * @return void
             * @throws Throwable
             */

    public static function handle(array $attributes): void
    {
        // This is where the action logic will be implemented.
    }
}
```
Or you can use more than one subfolder like this:.

```bash
php artisan make:action MyAction Folder1/Folder2
```
This will create a new action class in the `app/Actions/Folder1/Folder2/` directory with the name `MyAction.php`.




### Flags
- `--t` This flag prepare the action class with Database trasactions.
For example, if you want to create an action class with transactions, you can use:

```bash
php artisan make:action MyAction --t
```
will result in the following action class:
```php
<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;

use Throwable;

final class MyAction
{
         /**
             * Handle the action.
             *
             *
             * @param array $attributes
             * @return void
             * @throws Throwable
             */
            public static function handle(array $attributes): void
            {
                DB::transaction(function () use ($attributes) {
                  // Logic to be executed within the transaction
                });
    }
}
```

- `--u` This flag inyect User $user in the handle method.

For example, if you want to create an action class with User injection, you can use:

```bash
php artisan make:action MyAction --u
```
will result in the following action class:
```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Throwable;

final class MyAction
{
            /**
             * Handle the action.
             *
              * @param User $user
             * @param array $attributes
             * @return void
             * @throws Throwable
             */

    public static function handle(User $user,array $attributes): void
    {
        // This is where the action logic will be implemented.
    }
}
```

- `--tu` or `--ut`  Use both flags together to prepare the action class with Database transactions and User injection.

```bash php artisan make:action MyAction --tu ``` or ```bash php artisan make:action MyAction --ut ```

will result in the following action class:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Throwable;

final class MyAction
{
         /**
             * Handle the action.
             *
              * @param User $user
             * @param array $attributes
             * @return void
             * @throws Throwable
             */
            public static function handle(User $user,array $attributes): void
            {
                DB::transaction(function () use ($attributes) {
                  // Logic to be executed within the transaction
                });
    }
}

 
```


## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://github.com/PanchoDP/laravel-actions/blob/master/LICENSE.md)