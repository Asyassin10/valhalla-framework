<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Support\CommandSupport;
use Valhalla\Framework\Containers\ContainerTemplates;

final class InstallRuntimeCommand implements Command
{
    public function __construct(private readonly string $runtime)
    {
    }

    public function signature(): string
    {
        return sprintf('install:%s', $this->runtime);
    }

    public function description(): string
    {
        return sprintf('Generate a %s container setup.', $this->runtime);
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $binary = $this->runtime === 'docker' ? 'docker' : 'podman-compose';

        if (! CommandSupport::binaryExists($binary)) {
            $console->error(sprintf('The [%s] binary was not found on this machine.', $binary));

            return 1;
        }

        $phpVersion = $console->choice('PHP version', ['8.2', '8.3'], '8.2');
        $database = $console->choice('Database', ['mysql', 'postgres', 'none'], 'mysql');
        $dbDatabase = 'valhalla';
        $dbUsername = 'valhalla';
        $dbPassword = 'secret';

        if ($database !== 'none') {
            $dbDatabase = $console->ask('Database name', 'valhalla');
            $dbUsername = $console->ask('Database username', 'valhalla');
            $dbPassword = $console->ask('Database password', 'secret');
        }

        $cache = $console->choice('Cache', ['redis', 'none'], 'none');
        $queue = $console->confirm('Add a queue worker service?', false) ? 'yes' : 'no';
        $port = $console->ask('HTTP port to expose', '8080');

        $options = [
            'runtime' => $this->runtime,
            'php' => $phpVersion,
            'database' => $database,
            'db_database' => $dbDatabase,
            'db_username' => $dbUsername,
            'db_password' => $dbPassword,
            'cache' => $cache,
            'queue' => $queue,
            'port' => $port,
        ];

        $dockerDir = $context->workingPath().'/docker';
        @mkdir($dockerDir.'/nginx', 0777, true);
        if ($database === 'mysql') {
            @mkdir($dockerDir.'/mysql', 0777, true);
        }
        @mkdir($context->workingPath().'/config', 0777, true);

        $composeFile = $this->runtime === 'docker' ? 'docker-compose.yml' : 'podman-compose.yml';
        CommandSupport::writeFile($dockerDir.'/Dockerfile', ContainerTemplates::dockerfile($phpVersion, $database === 'none' ? null : $database, $cache === 'none' ? null : $cache), $console);
        CommandSupport::writeFile($dockerDir.'/'.$composeFile, ContainerTemplates::compose($options), $console);
        CommandSupport::writeFile($dockerDir.'/nginx/nginx.conf', ContainerTemplates::nginxConfig(), $console);
        CommandSupport::writeFile($dockerDir.'/.dockerignore', ContainerTemplates::dockerIgnore(), $console);
        CommandSupport::writeFile($context->workingPath().'/config/container.php', ContainerTemplates::config($this->runtime), $console);

        if ($database === 'mysql') {
            CommandSupport::writeFile($dockerDir.'/mysql/init.sql', ContainerTemplates::mysqlInit($dbDatabase), $console);
        }

        if (! is_file($context->workingPath().'/config/database.php')) {
            CommandSupport::writeFile($context->workingPath().'/config/database.php', ContainerTemplates::databaseConfig(), $console, false);
        }

        if (is_file($context->workingPath().'/.env.example')) {
            $env = [
                'DB_HOST' => $database === 'none' ? '127.0.0.1' : $database,
                'DB_PORT' => $database === 'postgres' ? '5432' : '3306',
                'DB_DATABASE' => $dbDatabase,
                'DB_USERNAME' => $dbUsername,
                'DB_PASSWORD' => $dbPassword,
            ];

            if ($database === 'none') {
                unset($env['DB_HOST'], $env['DB_PORT'], $env['DB_DATABASE'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
            }

            if ($cache === 'redis') {
                $env['REDIS_HOST'] = 'redis';
            }

            CommandSupport::appendEnvValues($context->workingPath().'/.env.example', $env);
        }

        $console->line(sprintf('Generated %s container files in [%s].', $this->runtime, $dockerDir));

        return 0;
    }
}
