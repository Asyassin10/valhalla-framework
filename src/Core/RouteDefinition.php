<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

final class RouteDefinition
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly mixed $handler,
        public readonly array $middleware = []
    ) {
    }
}
