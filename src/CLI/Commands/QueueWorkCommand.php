<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class QueueWorkCommand implements Command
{
    public function signature(): string
    {
        return 'queue:work';
    }

    public function description(): string
    {
        return 'Run the Valhalla queue worker loop.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $once = in_array('--once', $arguments, true);
        $console->line('Queue worker started. Waiting for jobs...');

        if ($once) {
            $console->line('No queue backend is configured yet.');

            return 0;
        }

        while (true) {
            sleep(5);
        }
    }
}
