---
name: laravel-actions-development
description: Generate and organize single-purpose Action classes in a Laravel project using the panchodp/laravel-actions package and its make:action command.
---

# Laravel Actions Development

## When to use this skill

Use this skill when the project depends on `panchodp/laravel-actions` and you need to:

- Create a new single-purpose Action class (instead of putting business logic in controllers, models, or services).
- Generate Actions with database transactions, an injected `User`, or a dedicated `Request` class.
- Inspect or organize the Actions directory tree.

## What this package does

`panchodp/laravel-actions` provides the `make:action` Artisan command to scaffold Action classes — small, final, single-responsibility classes that encapsulate one piece of business logic. Generated classes use `declare(strict_types=1)`, are `final`, and expose a single method (default `handle`).

## Creating actions

Always prefer the `make:action` command over writing Action classes by hand, so naming, namespace, and stub conventions stay consistent. Name Actions as **Verb + Noun in PascalCase** (e.g. `CreateInvoice`, `ProcessLote`, `SendNotification`) and keep one responsibility per Action.

```bash
# Basic action -> app/Actions/MyAction.php
php artisan make:action MyAction

# Group by domain using subfolders (forward or backslashes both work)
php artisan make:action Invoice/CreateInvoice
php artisan make:action User/Auth/Login
php artisan make:action Admin\\DeletePost
```

Generated output:

```php
<?php

declare(strict_types=1);

namespace App\Actions\User;

final class CreateAccount
{
    public function handle(array $attributes): void
    {
        // This is where the action logic will be implemented.
    }
}
```

## Flags

Flags are combinable in any order, just like `make:model`.

| Flag | Shortcut | Description |
|------|----------|-------------|
| `--transaction` | `-t` | Wraps the method body in `DB::transaction` |
| `--user` | `-u` | Injects `User $user` into the method |
| `--request` | `-r` | Generates a `Request` class and injects it into the method |
| `--static` | `-s` | Generates a `static` method instead of an instance method (avoid by default — see Conventions) |
| `--force` |  | Overwrites the action if it already exists |

```bash
php artisan make:action MyAction -tur    # transaction + user + request
php artisan make:action MyAction -turs   # + static method
php artisan make:action MyAction --transaction --user --request
```

Example for `-turs`:

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

## Calling actions

```php
// Resolved from the container (preferred — enables dependency injection)
$result = app(CreateInvoice::class)->handle($attributes);

// Plain instance
$result = (new ProcessLote())->handle($attributes);

// Static method (only when generated with --static / -s)
$result = QuickCalculation::handle($a, $b);
```

Prefer resolving Actions from the container with `app(MyAction::class)` so constructor dependencies are injected automatically.

## Listing actions

```bash
php artisan actions:list
```

Renders a tree of the Actions directory (the `base_folder` from config), showing every Action class and subfolder.

## Configuration

Config lives in `config/laravel-actions.php`:

- `base_folder` — where Actions are created. Default `Actions` (`app/Actions`).
- `method_name` — the generated method name. Default `handle`.

Respect these config values when referring to generated Actions; do not assume `app/Actions` or `handle` if the project customized them.

## Conventions for AI agents

- **Prefer instance methods over static.** Generate Actions without `-s` by default and call them with `app(MyAction::class)->handle(...)`. Instance methods support constructor dependency injection, are easier to mock and test, and can implement contracts. Only use `-s` for genuinely stateless, dependency-free helpers (e.g. a pure calculation) where DI and testability are not a concern.
- Keep each Action focused on a single responsibility; do not bundle unrelated logic.
- Use `-r` to generate validation in a dedicated `Request` rather than validating inline.
- Use `-t` when the operation must be atomic across multiple writes.
- Do not hand-roll Action files when `make:action` can generate them.
