<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Throwable;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\ORM\OrmManager;

final class MakeMigrationCommand implements Command
{
    public function signature(): string
    {
        return 'make:migration';
    }

    public function description(): string
    {
        return 'Generate an ORM migration.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $name = $arguments[0] ?? 'create_table';

        try {
            $manager = new OrmManager($context->config(), $context->workingPath());
            $path = $manager->driver()->makeMigration($name);
            $console->line(sprintf('Created migration: %s', $path));

            return 0;
        } catch (Throwable $throwable) {
            $console->error($throwable->getMessage());

            return 1;
        }
    }
}
