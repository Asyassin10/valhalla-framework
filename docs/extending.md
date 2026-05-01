# Extending Valhalla

## Controllers

```bash
./bin/valhalla make:controller UsersController
```

Add the generated controller to `routes/api.php`.

```php
use App\Controllers\UsersController;
use App\Middleware\AuditMiddleware;
use Valhalla\Framework\Facades\Route;

Route::get('/users', [UsersController::class, 'index'], [AuditMiddleware::class]);
```

## Middleware

```bash
./bin/valhalla make:middleware AuditMiddleware
```

Attach it to a route:

```php
use Valhalla\Framework\Facades\Route;

Route::get('/users', [UsersController::class, 'index'], [AuditMiddleware::class]);
```

## Services

```bash
./bin/valhalla make:service InventoryService
```

Inject or instantiate it where needed inside controllers or agents.

## Auth

- update JWT settings in `config/auth.php`
- add service tokens in `api_tokens`
- attach `AuthMiddleware` to any protected route

## Agents

- register with `agent:install`
- replace the default echo handler with your own `AgentTaskHandler`
- extend the CLI or bootstrap to bind named handlers
