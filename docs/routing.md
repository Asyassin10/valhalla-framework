# Routing

Valhalla routers are API-first and return JSON responses.

```php
$router->get('/health', fn () => Response::json(['ok' => true]));

$router->get('/users/{id}', fn (Request $request) => Response::json([
    'id' => $request->route('id'),
]));

$router->group('/internal', [AuthMiddleware::class], function ($router): void {
    $router->get('/status', fn () => Response::json(['status' => 'ok']));
});
```

Supported HTTP methods in v1:

- `GET`
- `POST`
- `PUT`
- `DELETE`

Each route can attach middleware as objects or class names implementing `MiddlewareInterface`.
