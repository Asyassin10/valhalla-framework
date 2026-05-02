<?php

declare(strict_types=1);

namespace Valhalla\Framework\Agents;

final class AgentRegistry
{
    public function __construct(private readonly string $path)
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (! is_file($path)) {
            file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function all(): array
    {
        $contents = file_get_contents($this->path) ?: '[]';
        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function set(string $name, array $config): void
    {
        $agents = $this->all();
        $agents[$name] = $config;
        file_put_contents($this->path, json_encode($agents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function get(string $name): ?array
    {
        $agents = $this->all();

        return $agents[$name] ?? null;
    }
}
