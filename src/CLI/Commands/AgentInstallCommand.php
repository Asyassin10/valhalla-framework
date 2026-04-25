<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Agents\AgentRegistry;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentInstallCommand implements Command
{
    public function signature(): string
    {
        return 'agent:install';
    }

    public function description(): string
    {
        return 'Register a new local Valhalla agent.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? null;
        $port = (int) ($arguments[1] ?? $context->config()->get('agents.default_port', 9501));
        $host = (string) $context->config()->get('agents.default_host', '127.0.0.1');

        if ($name === null) {
            $console->error('Usage: valhalla agent:install NAME [port]');
            return 1;
        }

        $registry = new AgentRegistry((string) $context->config()->get('agents.registry'));
        $registry->set($name, ['host' => $host, 'port' => $port]);

        $console->line(sprintf('Agent [%s] installed on %s:%d', $name, $host, $port));
        return 0;
    }
}
