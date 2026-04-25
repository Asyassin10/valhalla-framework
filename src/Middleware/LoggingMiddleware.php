<?php

declare(strict_types=1);

namespace Valhalla\Framework\Middleware;

use Valhalla\Framework\Core\MiddlewareInterface;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        error_log(sprintf(
            '[Valhalla] %s %s %d %sms',
            $request->method(),
            $request->path(),
            $response->status(),
            $duration
        ));

        return $response;
    }
}
