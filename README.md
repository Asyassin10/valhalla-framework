<p align="center">
  <img src="./assets/valhalla-logo.png" alt="Valhalla Logo" width="260">
</p>

# Valhalla

Valhalla is a microservices-first PHP framework for building lightweight JSON APIs, internal service endpoints, CLI-driven workflows, and local agent-style workers.

It is designed for teams that want a small, focused foundation instead of a full-stack monolith. Valhalla keeps the surface area intentionally compact: routing, middleware, authentication, service-to-service HTTP calls, scaffolding, and a local CLI for fast development.

## Why Valhalla

- API-first architecture with JSON responses as the default
- Lightweight core with minimal abstraction overhead
- CLI scaffolding for projects, controllers, middleware, and services
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

Once the CLI is installed globally, create a new service from anywhere:

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

Valhalla ships with a project and operations CLI.

```text
new project            Create a new Valhalla API project.
make:controller        Generate a controller class.
make:middleware        Generate a middleware class.
make:service           Generate a service helper class.
install                Install Composer dependencies.
auth:generate          Generate a JWT for a sample or provided user.
routes:list            List registered routes from routes/api.php.
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

List your registered routes:

```bash
valhalla routes:list
```

Generate a JWT for testing:

```bash
valhalla auth:generate 1 "Jane Doe"
```

## Example API Route

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
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Middleware\AuthMiddleware;

Route::get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
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
docs/                    Documentation guides
examples/basic-service/  Sample Valhalla service
public/                  HTTP entrypoint
routes/                  Route definitions
src/                     Framework source code
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
