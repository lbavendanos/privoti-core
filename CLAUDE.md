# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Privoti Core is a Laravel 12 application (PHP 8.4+) implementing a multi-tenant e-commerce platform with two separate domains:
- **CMS Domain** (`/c` prefix): Admin interface for managing products, customers, orders, etc.
- **Store Domain** (`/s` prefix): Customer-facing storefront interface

The application uses a **Domain-Driven Design** pattern with Actions for business logic.

## Architecture

### Domain Structure

The codebase is organized into two primary domains located in `app/Domains/`:

1. **Cms Domain** (`app/Domains/Cms/`)
   - Routes: `app/Domains/Cms/Routes/api.php`
   - Controllers: `app/Domains/Cms/Http/Controllers/`
   - Requests: `app/Domains/Cms/Http/Requests/`
   - Resources: `app/Domains/Cms/Http/Resources/`
   - Notifications: `app/Domains/Cms/Notifications/`
   - Console: `app/Domains/Cms/Console/`

2. **Store Domain** (`app/Domains/Store/`)
   - Routes: `app/Domains/Store/Routes/api.php`
   - Controllers: `app/Domains/Store/Http/Controllers/`
   - Middleware: `app/Domains/Store/Http/Middleware/`
   - Requests: `app/Domains/Store/Http/Requests/`
   - Resources: `app/Domains/Store/Http/Resources/`
   - Notifications: `app/Domains/Store/Notifications/`

### Action Pattern

Business logic is encapsulated in **Action classes** located in `app/Actions/`, organized by entity:
- `app/Actions/Product/`
- `app/Actions/Customer/`
- `app/Actions/Collection/`
- `app/Actions/Vendor/`
- `app/Actions/User/`
- etc.

Actions follow a consistent pattern:
- Final readonly classes
- Constructor dependency injection
- Single `handle()` method
- Named with verb suffixes: `CreateProductAction`, `UpdateProductAction`, `GetProductsAction`, etc.

**Example workflow**: Controller → Request Validation → Action → Model → Resource

### Authentication

Two separate authentication guards defined in `config/auth.php`:
- **cms guard**: Session-based auth for `User` model (admin users)
- **store guard**: Session-based auth for `Customer` model (storefront customers)

Routes are protected with corresponding middleware: `auth:cms` or `auth:store`

## Development Commands

### Setup
```bash
composer install                    # Install dependencies
cp .env.example .env               # Copy environment file
./vendor/bin/sail up -d            # Start Docker containers
./vendor/bin/sail artisan key:generate  # Generate application key
./vendor/bin/sail artisan migrate  # Run migrations
./vendor/bin/sail artisan db:seed  # Seed database (if seeders exist)
```

### Testing
```bash
./vendor/bin/sail artisan test     # Run all tests (Pest)
./vendor/bin/sail artisan test --filter=ProductTest  # Run specific test
./vendor/bin/sail artisan test --parallel  # Run tests in parallel
```

### Code Quality
```bash
./vendor/bin/sail bin pint         # Format code (Laravel Pint)
./vendor/bin/sail bin pint --test  # Check formatting without changes
./vendor/bin/sail bin phpstan analyse  # Static analysis (Larastan at max level)
./vendor/bin/sail bin rector process  # Refactor code
./vendor/bin/sail bin rector process --dry-run  # Preview rector changes
```

### Development
```bash
./vendor/bin/sail up               # Start development server
./vendor/bin/sail artisan tinker   # Interactive REPL
./vendor/bin/sail artisan pail     # Tail application logs
./vendor/bin/sail artisan route:list  # List all routes
./vendor/bin/sail artisan migrate:fresh --seed  # Fresh database with seeds
./vendor/bin/sail down             # Stop Docker containers
```

## Code Standards

### Strict Types
All PHP files must declare strict types at the top:
```php
<?php

declare(strict_types=1);
```

### Code Style (Pint Configuration)
- Laravel preset with custom rules in `pint.json`
- Final classes enforced (`final_class: true`)
- Strict comparisons required (`strict_comparison: true`)
- Global namespace imports enabled for classes, constants, and functions
- Ordered class elements: traits → constants → properties → constructor → methods
- Date/time immutable enforced

### Static Analysis (PHPStan/Larastan)
- Level: `max` (configured in `phpstan.neon`)
- Paths analyzed: `app/`
- Excludes: `vendor/`, `storage/`
- Universal object crates: `JsonResource`

### Refactoring (Rector)
- Configured in `rector.php`
- Paths: `app/`, `bootstrap/app.php`, `database/`, `public/`
- Rule sets: Dead Code, Code Quality, Type Declaration, Privatization, Early Return, Laravel code quality
- Skip: Override attributes, privatization on Models

## Testing

### Framework
Tests use **Pest** (not PHPUnit syntax) with Laravel plugin.

### Test Structure
- Feature tests: `tests/Feature/` (organized by domain)
- Unit tests: `tests/Unit/` (organized by layer: Actions, Models)
- Base configuration: `tests/Pest.php`
- All tests use `RefreshDatabase` trait by default

### Running Specific Tests
```bash
./vendor/bin/sail artisan test tests/Feature/Domains/Cms/ProductTest.php
./vendor/bin/sail artisan test tests/Unit/Actions/CreateProductActionTest.php
```

## Models

Key models in `app/Models/`:
- `User` - CMS admin users
- `Customer` - Store customers
- `Product` - Products with variants, options, media
- `ProductVariant` - Product variants
- `ProductOption` - Product options (e.g., Size, Color)
- `ProductOptionValue` - Option values (e.g., Small, Red)
- `ProductMedia` - Product images/videos
- `ProductCategory` - Product categorization
- `ProductType` - Product types
- `Collection` - Product collections
- `CustomerAddress` - Customer shipping/billing addresses
- `Vendor` - Product vendors

## Important Patterns

### Creating New Actions
1. Create action class in appropriate `app/Actions/{Entity}/` directory
2. Make class `final readonly`
3. Inject dependencies in constructor
4. Implement single `handle()` method
5. Add PHPDoc blocks for arrays with proper typing
6. Use database transactions for multi-step operations

### Creating New Controllers
1. Place in appropriate domain: `app/Domains/{Cms|Store}/Http/Controllers/`
2. Make class `final`
3. Inject actions as method parameters (not constructor)
4. Return Resources, not raw models
5. Use Form Requests for validation

### Adding Routes
1. CMS routes: Add to `app/Domains/Cms/Routes/api.php` (under `/c` prefix)
2. Store routes: Add to `app/Domains/Store/Routes/api.php` (under `/s` prefix)
3. Apply appropriate authentication middleware (`auth:cms` or `auth:store`)
4. Verified users only for sensitive operations (`verified` middleware)

## Database Migrations

Migrations are timestamped in `database/migrations/`. Core tables include:
- Users and authentication
- Customers and addresses
- Products with full variant/option system
- Collections, categories, types
- Vendors

Run migrations with:
```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh  # Reset and re-run
```

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.4
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `./vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `./vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `./vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `./vendor/bin/sail artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `./vendor/bin/sail bin pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `./vendor/bin/sail bin pint --test`, simply run `./vendor/bin/sail bin pint` to fix any formatting issues.


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `./vendor/bin/sail artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `./vendor/bin/sail artisan test`.
- To run all tests in a file: `./vendor/bin/sail artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `./vendor/bin/sail artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `./vendor/bin/sail artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
