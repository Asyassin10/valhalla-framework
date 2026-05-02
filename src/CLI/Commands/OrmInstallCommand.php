<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Support\CommandSupport;
use Valhalla\Framework\Containers\ContainerTemplates;

final class OrmInstallCommand implements Command
{
    public function signature(): string
    {
        return 'orm:install';
    }

    public function description(): string
    {
        return 'Install an ORM driver (eloquent or doctrine).';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $driver = $arguments[0] ?? '';

        if (! in_array($driver, ['eloquent', 'doctrine'], true)) {
            $console->error('Usage: valhalla orm:install [eloquent|doctrine]');

            return 1;
        }

        $ormConfig = $context->workingPath().'/config/orm.php';
        if (is_file($ormConfig)) {
            $installedDriver = require $ormConfig;
            $configuredDriver = is_array($installedDriver) ? ($installedDriver['driver'] ?? 'unknown') : 'unknown';
            $console->error(sprintf('ORM driver [%s] is already installed. Run [valhalla orm:remove] first.', $configuredDriver));

            return 1;
        }

        $packages = $driver === 'eloquent'
            ? 'composer require illuminate/database'
            : 'composer require doctrine/orm doctrine/migrations';
        $directory = $driver === 'eloquent' ? 'src/Models' : 'src/Entities';

        @mkdir($context->workingPath().'/'.$directory, 0777, true);
        @mkdir($context->workingPath().'/database/migrations', 0777, true);
        @mkdir($context->workingPath().'/config', 0777, true);

        if (! CommandSupport::writeFile($ormConfig, ContainerTemplates::ormConfig($driver), $console, false)) {
            return 1;
        }

        if (! is_file($context->workingPath().'/config/database.php')) {
            CommandSupport::writeFile(
                $context->workingPath().'/config/database.php',
                ContainerTemplates::databaseConfig(),
                $console,
                false
            );
        }

        if (is_file($context->workingPath().'/.env.example')) {
            CommandSupport::appendEnvValues($context->workingPath().'/.env.example', [
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => '3306',
                'DB_DATABASE' => 'valhalla',
                'DB_USERNAME' => 'valhalla',
                'DB_PASSWORD' => 'secret',
            ]);
        }

        $exitCode = CommandSupport::run($packages, $context->workingPath());

        if ($exitCode !== 0) {
            @unlink($ormConfig);
            $console->error('ORM configuration was created, but Composer install failed.');

            return $exitCode;
        }

        $console->line(sprintf('Installed [%s] ORM support.', $driver));

        return 0;
    }
}
