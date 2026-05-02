# Routing

Valhalla routing is API-first and returns JSON responses by default.

## Route Facade

The recommended style is importing `Route` and defining routes statically.

```php
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

Route::get('/health', fn () => Response::json(['ok' => true]));
Route::post('/orders', fn () => Response::json(['created' => true], 201));

Route::group('/api', [], function (): void {
    Route::get('/status', fn () => Response::json(['status' => 'ok']));
});
```

## Closure Routes

Closures continue to work for small endpoints.

```php
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

Route::get('/users/{id}', fn (Request $request) => Response::json([
    'id' => $request->route('id'),
]));
```

## Controller Routes

You can register controller methods with `[Controller::class, 'method']`.

```php
use App\Controllers\UsersController;
use Valhalla\Framework\Facades\Route;

Route::get('/users', [UsersController::class, 'index']);
Route::get('/users/{id}', [UsersController::class, 'show']);
Route::post('/users', [UsersController::class, 'store']);
```

Generate a controller with:

```bash
valhalla make:controller Users
```

Generated controllers use `App\Controllers` and include a default `index` method.

## Attribute Routes

Valhalla supports PHP 8 attributes on controllers placed in `src/Controllers`. Those attribute routes are loaded automatically when you call `$app->loadRoutes(...)`.

```php
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Routing\Attributes\Get;
use Valhalla\Framework\Routing\Attributes\Middleware;
use Valhalla\Framework\Routing\Attributes\Post;

final class UserController
{
    #[Get('/users')]
    #[Middleware(App\Middleware\AuthMiddleware::class)]
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

If you need manual control for a specific controller, the low-level API still exists:

```php
$app->loadAttributeRoutes(UserController::class);
```

## Route Groups

Group routes with a shared prefix and middleware list.

```php
use App\Middleware\AuthMiddleware;
use Valhalla\Framework\Facades\Route;

Route::group('/internal', [AuthMiddleware::class], function (): void {
    Route::get('/metrics', fn () => ['ok' => true]);
    Route::get('/status', fn () => ['status' => 'healthy']);
});
```

Legacy callback style still works too:

```php
$router->group('/legacy', [], function ($router): void {
    $router->get('/ping', fn () => ['pong' => true]);
});
```

## Route Parameters

Use `{name}` placeholders in the URI and read them with `$request->route()`.

```php
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

Route::get('/orders/{id}', fn (Request $request) => Response::json([
    'order_id' => $request->route('id'),
]));
```

## VS Code Setup

Using `Route::` gives full autocomplete in editors like VS Code with Intelephense.

If you still prefer the legacy `$router` style, add this docblock at the top of `routes/api.php`:

```php
/** @var \Valhalla\Framework\Core\Router $router */
```

An `_ide_helper_routes.php` stub is also included at the project root for editor support.

## Listing Routes

Print registered routes with:

```bash
valhalla routes:list
```

Controller routes display as `ControllerName@method` in the output.
