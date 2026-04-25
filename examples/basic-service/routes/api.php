<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Middleware\AuthMiddleware;

$router->get('/health', new HealthController());
$router->get('/token', fn () => Response::json([
    'token' => Auth::generateToken(['id' => 11, 'name' => 'Basic Service']),
]));
$router->get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
