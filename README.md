<p align="center">
  <img src="./assets/valhalla-logo.png" alt="Valhalla Logo" width="260">
</p>

# Valhalla

Valhalla is a microservices-first PHP framework for building lightweight JSON APIs, internal service endpoints, CLI-driven workflows, local agent-style workers, optional ORM-backed applications, and container-ready services.

It is designed for teams that want a small, focused foundation instead of a full-stack monolith. Valhalla keeps the surface area intentionally compact: routing, middleware, authentication, service-to-service HTTP calls, scaffolding, agents, optional ORM drivers, and one-command container generation.

## Why Valhalla

- API-first architecture with JSON responses as the default
- Lightweight core with minimal abstraction overhead
- `Route::` facade with automatic attribute route discovery
- Optional ORM support with pluggable Eloquent or Doctrine drivers
- Container scaffolding for Docker and Podman
- CLI scaffolding for projects, controllers, middleware, services, models, and migrations
- JWT and API token authentication support
- Service-to-service HTTP client with retries and circuit-breaker support
- Local TCP-based agent workflow for task-style background services
- Simple structure that is easy to read, extend, and test

## Requirements

- PHP 8.2+
- Composer

## Install Valhalla Globally

Install the Valhalla CLI globally with Composer:

```bash
composer global require asyassin10/valhalla-framework
```

Make sure Composer's global `bin` directory is on your `PATH`.

For `zsh` on macOS:

```bash
echo 'export PATH="$PATH:$HOME/.composer/vendor/bin:$HOME/.config/composer/vendor/bin"' >> ~/.zshrc
source ~/.zshrc
```

Verify the installation:

```bash
valhalla
```

## Create a New Project

Create a new service from anywhere:

```bash
valhalla new project orders-service
cd orders-service
composer install
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Then open:

```text
http://127.0.0.1:8080/health
```

You can also scaffold and install dependencies in one step:

```bash
valhalla new project orders-service --install
```

Generated projects now include:

- `src/Controllers`, `src/Middleware`, `src/Services`
- `database/migrations`
- `config/database.php`
- route, auth, logging, agents, and service config files

ORM-specific directories are created only when you install an ORM driver:

- `valhalla orm:install eloquent` creates `src/Models`
- `valhalla orm:install doctrine` creates `src/Entities`

## Local Development In This Repository

If you are working on the framework itself:

```bash
composer install
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Run the CLI locally from the repository root:

```bash
php bin/valhalla
```

Or:

```bash
./bin/valhalla
```

## CLI Commands

Valhalla ships with a project, ORM, container, and operations CLI.

```text
new project            Create a new Valhalla API project.
make:controller        Generate a controller class.
make:middleware        Generate a middleware class.
make:service           Generate a service helper class.
install                Install Composer dependencies.
auth:generate          Generate a JWT for a sample or provided user.
routes:list            List registered routes from routes/api.php.
orm:install            Install an ORM driver (eloquent or doctrine).
orm:remove             Remove ORM configuration from the project.
migrate                Run ORM migrations.
migrate:rollback       Rollback the last ORM migration.
migrate:diff           Generate a Doctrine migration diff.
make:model             Generate an ORM model or entity.
make:migration         Generate an ORM migration.
install:docker         Generate a docker container setup.
install:podman         Generate a podman container setup.
up                     Start the configured container stack.
down                   Stop the configured container stack.
build                  Build the configured container stack.
logs                   Tail logs from the configured container stack.
shell                  Open a shell in the app container.
queue:work             Run the Valhalla queue worker loop.
agent:install          Register a new local Valhalla agent.
agent:start            Start a registered local agent in the background.
agent:stop             Stop a background Valhalla agent.
agent:call             Call a running local agent with a task.
agent:list             List installed agents.
agent:serve            Internal command used to boot an agent server process.
```

### Common Examples

Generate a controller:

```bash
valhalla make:controller OrdersController
```

Generate middleware:

```bash
valhalla make:middleware InternalAuth
```

Generate a service helper:

```bash
valhalla make:service BillingService
```

List registered routes:

```bash
valhalla routes:list
```

Generate a JWT for testing:

```bash
valhalla auth:generate 1 "Jane Doe"
```

Install Eloquent ORM support:

```bash
valhalla orm:install eloquent
valhalla make:model Order
valhalla make:migration create_orders_table
valhalla migrate
```

Install Doctrine ORM support:

```bash
valhalla orm:install doctrine
valhalla make:model Invoice
valhalla migrate:diff
valhalla migrate
```

Generate Docker files:

```bash
valhalla install:docker
valhalla build
valhalla up
valhalla logs
valhalla shell
```

Generate Podman files:

```bash
valhalla install:podman
valhalla build
valhalla up
```

## Routing

Valhalla is built around simple, readable route definitions.

```php
use App\Controllers\OrdersController;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

Route::get('/health', fn () => Response::json([
    'ok' => true,
    'service' => 'orders',
]));

Route::get('/users/{id}', fn (Request $request) => Response::json([
    'id' => $request->route('id'),
]));

Route::post('/orders', [OrdersController::class, 'store']);
```

The `Route` facade is the recommended style because it gives clean editor autocomplete and avoids `$router` variable warnings in IDEs.

### Route Groups

```php
use App\Middleware\AuthMiddleware;
use Valhalla\Framework\Facades\Route;

Route::group('/internal', [AuthMiddleware::class], function (): void {
    Route::get('/status', fn () => ['ok' => true]);
});
```

### Controller Arrays

```php
use App\Controllers\UsersController;
use Valhalla\Framework\Facades\Route;

Route::get('/users', [UsersController::class, 'index']);
Route::get('/users/{id}', [UsersController::class, 'show']);
Route::post('/users', [UsersController::class, 'store']);
```

### Attribute Routes

Controllers in `src/Controllers` can declare routes with PHP 8 attributes, and Valhalla loads them automatically when the app calls `loadRoutes(...)`.

```php
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Routing\Attributes\Get;
use Valhalla\Framework\Routing\Attributes\Post;

final class UserController
{
    #[Get('/users')]
    public function index(Request $request): Response
    {
        return Response::json(['users' => []]);
    }

    #[Post('/users')]
    public function store(Request $request): Response
    {
        return Response::json(['created' => true]);
    }
}
```

Default bootstrap stays simple:

```php
$app->loadRoutes(dirname(__DIR__).'/routes/api.php');
```

If you need manual control, the low-level method still exists:

```php
$app->loadAttributeRoutes(UserController::class);
```

## Authentication

Valhalla supports:

- JWT bearer authentication
- Static API tokens for internal service communication

Generate a test token:

```bash
valhalla auth:generate 1 "Jane Doe"
```

Protect a route with middleware:

```php
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Middleware\AuthMiddleware;

Route::get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
```

## Pluggable ORM System

Valhalla supports one optional ORM driver per project. Install either Eloquent or Doctrine, never both.

### ORM Config

`config/orm.php` is the source of truth:

```php
return [
    'driver' => 'eloquent',
];
```

or:

```php
return [
    'driver' => 'doctrine',
];
```

If `config/orm.php` does not exist, ORM support is skipped silently during application boot.

### Eloquent Driver

Install it with:

```bash
valhalla orm:install eloquent
```

This command:

- runs `composer require illuminate/database`
- creates `src/Models/`
- creates `database/migrations/`
- writes `config/orm.php`
- ensures `config/database.php` exists

Generate a model:

```bash
valhalla make:model Order
```

This creates a model under `src/Models/` extending `Illuminate\Database\Eloquent\Model` with `protected array $guarded = [];`.

Generate a migration:

```bash
valhalla make:migration create_orders_table
```

Run migrations:

```bash
valhalla migrate
```

Rollback the last migration:

```bash
valhalla migrate:rollback
```

### Doctrine Driver

Install it with:

```bash
valhalla orm:install doctrine
```

This command:

- runs `composer require doctrine/orm doctrine/migrations`
- creates `src/Entities/`
- creates `database/migrations/`
- writes `config/orm.php`
- ensures `config/database.php` exists

Generate an entity:

```bash
valhalla make:model Invoice
```

Doctrine entities are generated in `src/Entities/` with mapping attributes and a `getId()` getter. After adding fields, generate a diff migration with:

```bash
valhalla migrate:diff
```

Apply migrations:

```bash
valhalla migrate
```

Rollback to the previous Doctrine migration:

```bash
valhalla migrate:rollback
```

Remove ORM config:

```bash
valhalla orm:remove
```

This removes `config/orm.php` and tells you to remove the Composer package manually.

### Database Config

Valhalla reads database credentials from `config/database.php` and `.env` values such as:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=valhalla
DB_USERNAME=valhalla
DB_PASSWORD=secret
```

## Docker And Podman Setup

Valhalla can generate a complete container setup with a single command.

### Install Docker Files

```bash
valhalla install:docker
```

### Install Podman Files

```bash
valhalla install:podman
```

Both commands ask for:

- PHP version: `8.2` or `8.3`
- Database: `mysql`, `postgres`, or `none`
- Database name, username, and password when a database is enabled
- Cache: `redis` or `none`
- Queue worker: `yes` or `no`
- HTTP port to expose

Generated files are placed under `docker/`:

- `docker/Dockerfile`
- `docker/docker-compose.yml` or `docker/podman-compose.yml`
- `docker/nginx/nginx.conf`
- `docker/mysql/init.sql` when MySQL is selected
- `docker/.dockerignore`

Valhalla also writes `config/container.php`:

```php
return [
    'runtime' => 'docker',
];
```

or:

```php
return [
    'runtime' => 'podman',
];
```

### Container Runtime Commands

Once a runtime is installed, use:

```bash
valhalla build
valhalla up
valhalla logs
valhalla shell
valhalla down
```

These commands automatically read `config/container.php` to choose Docker or Podman.

### Queue Worker Container

If you choose a queue worker during container setup, Valhalla creates a worker service that runs:

```bash
php bin/valhalla queue:work
```

## Service-to-Service Calls

Valhalla includes a JSON-oriented HTTP client wrapper for internal services.

```php
use Valhalla\Framework\Services\ServiceClient;

$client = new ServiceClient($app->config());

$result = $client->json(
    'billing',
    'POST',
    'https://billing.internal/payments',
    ['invoice_id' => 99]
);
```

The service client supports retry settings and circuit-breaker configuration through `config/services.php`.

## Local Agents

Valhalla includes an MVP local agent system for task-based workers over newline-delimited JSON and TCP.

Install and start an agent:

```bash
valhalla agent:install summarizer 9501
valhalla agent:start summarizer
valhalla agent:list
```

Call an agent:

```bash
valhalla agent:call summarizer summarize
```

This is useful for internal automation, background jobs, or local task-oriented services.

## Project Structure

```text
bin/                     CLI entrypoint
config/                  Framework and application configuration
database/                Migration files for ORM-backed apps
docs/                    Documentation guides
docker/                  Generated Docker or Podman files
examples/basic-service/  Sample Valhalla service
public/                  HTTP entrypoint
routes/                  Route definitions
src/                     Framework source code and app classes
tests/                   PHPUnit tests
```

## Documentation

Detailed guides are available in the `docs/` directory:

- [Installation](./docs/installation.md)
- [Routing](./docs/routing.md)
- [Authentication](./docs/auth.md)
- [Agents](./docs/agents.md)
- [CLI](./docs/cli.md)
- [Services](./docs/services.md)
- [Extending Valhalla](./docs/extending.md)
- [Testing](./docs/testing.md)

## Example Service

A runnable example is included in:

```text
examples/basic-service
```

This example shows the expected project structure and gives you a starting point for your own services.

## Testing

Run the test suite from the repository root:

```bash
composer test
```

## Roadmap Direction

Valhalla is a strong fit for:

- internal APIs
- microservice backends
- service orchestration layers
- agent-like local workers
- ORM-backed services with optional Eloquent or Doctrine integration
- containerized API services with Docker or Podman
