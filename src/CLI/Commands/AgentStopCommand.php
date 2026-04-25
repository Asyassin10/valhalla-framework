<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class AgentStopCommand implements Command
{
    public function signature(): string
    {
        return 'agent:stop';
    }

    public function description(): string
    {
        return 'Stop a background Valhalla agent.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? null;
        $pidFile = (string) $context->config()->get('agents.pid_dir') . DIRECTORY_SEPARATOR . $name . '.pid';

        if ($name === null || !is_file($pidFile)) {
            $console->error('Agent PID file not found.');
            return 1;
        }

        $pid = trim((string) file_get_contents($pidFile));
        shell_exec('kill ' . escapeshellarg($pid));
        unlink($pidFile);

        $console->line(sprintf('Agent [%s] stopped.', $name));
        return 0;
    }
}
