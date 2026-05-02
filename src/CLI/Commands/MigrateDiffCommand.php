<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Throwable;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\ORM\Drivers\DoctrineDriver;
use Valhalla\Framework\ORM\OrmManager;

final class MigrateDiffCommand implements Command
{
    public function signature(): string
    {
        return 'migrate:diff';
    }

    public function description(): string
    {
        return 'Generate a Doctrine migration diff.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        try {
            $manager = new OrmManager($context->config(), $context->workingPath());
            $driver = $manager->driver();

            if (! $driver instanceof DoctrineDriver) {
                throw new \RuntimeException('The [migrate:diff] command is only available when Doctrine is installed.');
            }

            $driver->diff();
            $console->line('Doctrine migration diff generated.');

            return 0;
        } catch (Throwable $throwable) {
            $console->error($throwable->getMessage());

            return 1;
        }
    }
}
