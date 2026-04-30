<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\Agents\AgentRegistry;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentStartCommand implements Command
{
    public function signature(): string
    {
        return 'agent:start';
    }

    public function description(): string
    {
        return 'Start a registered local agent in the background.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? null;

        if ($name === null) {
            $console->error('Usage: valhalla agent:start NAME');

            return 1;
        }

        $registry = new AgentRegistry((string) $context->config()->get('agents.registry'));
        $agent = $registry->get($name);

        if ($agent === null) {
            $console->error(sprintf('Agent [%s] is not installed.', $name));

            return 1;
        }

        $pidDir = (string) $context->config()->get('agents.pid_dir');
        @mkdir($pidDir, 0777, true);
        $log = storage_path('logs/'.$name.'.log');
        @mkdir(dirname($log), 0777, true);

        $command = sprintf(
            'cd %s && php %s/bin/valhalla agent:serve %s %d > %s 2>&1 & echo $!',
            escapeshellarg($context->workingPath()),
            escapeshellarg($context->installPath()),
            escapeshellarg($name),
            (int) $agent['port'],
            escapeshellarg($log)
        );

        $pid = trim((string) shell_exec($command));
        file_put_contents($pidDir.DIRECTORY_SEPARATOR.$name.'.pid', $pid);

        $console->line(sprintf('Agent [%s] started with PID %s', $name, $pid));

        return 0;
    }
}
