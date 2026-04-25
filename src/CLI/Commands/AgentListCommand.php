<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Agents\AgentRegistry;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentListCommand implements Command
{
    public function signature(): string
    {
        return 'agent:list';
    }

    public function description(): string
    {
        return 'List installed agents.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $registry = new AgentRegistry((string) $context->config()->get('agents.registry'));

        foreach ($registry->all() as $name => $agent) {
            $console->line(sprintf('%s  %s:%d', $name, $agent['host'], $agent['port']));
        }

        return 0;
    }
}
