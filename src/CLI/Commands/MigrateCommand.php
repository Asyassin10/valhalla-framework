<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Throwable;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\ORM\OrmManager;

final class MigrateCommand implements Command
{
    public function signature(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run ORM migrations.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        try {
            $manager = new OrmManager($context->config(), $context->workingPath());
            $manager->boot();
            $manager->driver()->migrate();
            $console->line('Migrations completed.');

            return 0;
        } catch (Throwable $throwable) {
            $console->error($throwable->getMessage());

            return 1;
        }
    }
}
