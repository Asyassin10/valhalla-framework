<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Exception;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $key, callable $resolver): void
    {
        $this->bindings[$key] = [
            'resolver' => $resolver,
            'singleton' => false,
        ];
    }

    public function singleton(string $key, callable $resolver): void
    {
        $this->bindings[$key] = [
            'resolver' => $resolver,
            'singleton' => true,
        ];
    }

    public function make(string $key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        if (! isset($this->bindings[$key])) {
            throw new Exception("Service {$key} not found.");
        }

        $binding = $this->bindings[$key];

        $object = $binding['resolver']($this);

        if ($binding['singleton']) {
            $this->instances[$key] = $object;
        }

        return $object;
    }
}
