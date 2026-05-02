<?php

declare(strict_types=1);

/** @var \Valhalla\Framework\Core\Router $router */

use App\Controllers\HealthController;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Middleware\AuthMiddleware;

Route::get('/health', [HealthController::class, 'index']);

Route::get('/users/{id}', fn (Request $request) => Response::json([
    'user_id' => $request->route('id'),
]));

Route::get('/token', fn () => Response::json([
    'token' => Auth::generateToken(['id' => 1, 'name' => 'Framework Demo']),
]));

Route::group('/internal', [], function (): void {
    Route::get('/status', fn () => Response::json(['cluster' => 'healthy']));
});

Route::get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
