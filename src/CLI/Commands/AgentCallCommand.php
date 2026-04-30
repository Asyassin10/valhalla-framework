<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Agents\AgentClient;
use Valhalla\Framework\Agents\AgentRegistry;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentCallCommand implements Command
{
    public function signature(): string
    {
        return 'agent:call';
    }

    public function description(): string
    {
        return 'Call a running local agent with a task.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? null;
        $task = $arguments[1] ?? null;

        if ($name === null || $task === null) {
            $console->error('Usage: valhalla agent:call NAME TASK');

            return 1;
        }

        $registry = new AgentRegistry((string) $context->config()->get('agents.registry'));
        $agent = $registry->get($name);

        if ($agent === null) {
            $console->error(sprintf('Agent [%s] is not installed.', $name));

            return 1;
        }

        $client = new AgentClient;
        $response = $client->call((string) $agent['host'], (int) $agent['port'], $task, ['source' => 'cli']);
        $console->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return 0;
    }
}
