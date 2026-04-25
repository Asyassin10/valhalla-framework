<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
