<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Middleware\AuthMiddleware;

Route::get('/health', [HealthController::class, 'index']);
Route::get('/token', fn () => Response::json([
    'token' => Auth::generateToken(['id' => 11, 'name' => 'Basic Service']),
]));
Route::get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
