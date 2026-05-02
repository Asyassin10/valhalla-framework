<?php

declare(strict_types=1);

namespace Valhalla\Framework\Routing\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Middleware
{
    public readonly array $middleware;

    public function __construct(string ...$middleware)
    {
        $this->middleware = $middleware;
    }
}
