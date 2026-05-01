<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Throwable;
use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\ORM\OrmManager;

final class MigrateRollbackCommand implements Command
{
    public function signature(): string
    {
        return 'migrate:rollback';
    }

    public function description(): string
    {
        return 'Rollback the last ORM migration.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        try {
            $manager = new OrmManager($context->config(), $context->workingPath());
            $manager->boot();
            $manager->driver()->rollback();
            $console->line('Rollback completed.');

            return 0;
        } catch (Throwable $throwable) {
            $console->error($throwable->getMessage());

            return 1;
        }
    }
}
