<?php

declare(strict_types=1);

use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Middleware\AuthMiddleware;

$router->get('/health', fn (Request $request) => Response::json([
    'service' => 'valhalla',
    'status' => 'ok',
    'path' => $request->path(),
]));

$router->get('/users/{id}', fn (Request $request) => Response::json([
    'user_id' => $request->route('id'),
]));

$router->get('/token', fn () => Response::json([
    'token' => Auth::generateToken(['id' => 1, 'name' => 'Framework Demo']),
]));

$router->group('/internal', [], function ($router): void {
    $router->get('/status', fn () => Response::json(['cluster' => 'healthy']));
});

$router->get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
