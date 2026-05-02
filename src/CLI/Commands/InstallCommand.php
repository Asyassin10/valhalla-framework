<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;

final class InstallCommand implements Command
{
    public function signature(): string
    {
        return 'install';
    }

    public function description(): string
    {
        return 'Install Composer dependencies.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $command = sprintf('cd %s && composer install', escapeshellarg($context->workingPath()));
        passthru($command, $exitCode);
        $console->line($exitCode === 0 ? 'Dependencies installed.' : 'Composer install failed.');

        return $exitCode;
    }
}
