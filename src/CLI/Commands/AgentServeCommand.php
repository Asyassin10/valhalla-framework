<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Agents\AgentServer;
use Valhalla\Framework\Agents\EchoAgentHandler;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentServeCommand implements Command
{
    public function signature(): string
    {
        return 'agent:serve';
    }

    public function description(): string
    {
        return 'Internal command used to boot an agent server process.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? 'agent';
        $port = (int) ($arguments[1] ?? $context->config()->get('agents.default_port', 9501));
        $host = (string) $context->config()->get('agents.default_host', '127.0.0.1');

        $console->line(sprintf('Serving agent [%s] on %s:%d', $name, $host, $port));
        (new AgentServer($host, $port, new EchoAgentHandler()))->run();
        return 0;
    }
}
