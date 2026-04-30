<?php

declare(strict_types=1);

namespace Valhalla\Framework\Support;

final class Config
{
    private array $items = [];

    public function __construct(private readonly string $basePath) {}

    public function load(string $directory = 'config'): void
    {
        $configPath = rtrim($this->basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.trim($directory, DIRECTORY_SEPARATOR);

        if (! is_dir($configPath)) {
            return;
        }

        foreach (glob($configPath.DIRECTORY_SEPARATOR.'*.php') ?: [] as $file) {
            $name = basename($file, '.php');
            $this->items[$name] = require $file;
        }
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
