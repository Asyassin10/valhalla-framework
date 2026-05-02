<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Throwable;

final class ExceptionPipeline
{
    /** @var callable[] */
    private array $handlers = [];

    public function add(callable $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function handle(Throwable $e): void
    {
        foreach ($this->handlers as $handler) {
            $handler($e);
        }
    }
}
