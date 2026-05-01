<?php

declare(strict_types=1);

namespace Valhalla\Framework\Middleware;

use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Exceptions\AuthenticationException;
use Valhalla\Framework\Core\MiddlewareInterface;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        Auth::attempt($request);

        if (! Auth::check()) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
