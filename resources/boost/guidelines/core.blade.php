## Laravel Actions (panchodp/laravel-actions)

This project uses `panchodp/laravel-actions` to scaffold single-purpose Action classes via the `make:action` Artisan command. Encapsulate reusable business logic in Actions instead of controllers, models, or fat services.

### Prefer instance methods over static

Generate Actions as **instance methods** (the default) and resolve them from the container:

@verbatim
<code-snippet name="Preferred: instance method via the container" lang="php">
$result = app(\App\Actions\CreateInvoice::class)->handle($attributes);
</code-snippet>
@endverbatim

Only use the static flag (`-s`) for genuinely stateless, dependency-free helpers such as a pure calculation. Instance methods support constructor dependency injection, are easier to mock and test, and can implement contracts — so they are the default choice.

@verbatim
<code-snippet name="Generate an instance Action (default)" lang="shell">
php artisan make:action Invoice/CreateInvoice
</code-snippet>
@endverbatim

Combine flags (single dash) only when needed: `-t` (DB transaction), `-u` (inject `User`), `-r` (generate a `Request`). For example `php artisan make:action CreateInvoice -tur`. Name Actions as Verb + Noun in PascalCase, one responsibility each.