<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

final class Container
{
    private array $instances = [];

    public function singleton(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function get(string $id): object
    {
        if (!array_key_exists($id, $this->instances)) {
            $this->instances[$id] = new $id();
        }

        return $this->instances[$id];
    }
}
