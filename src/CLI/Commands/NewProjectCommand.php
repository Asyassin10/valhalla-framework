<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Templates;

final class NewProjectCommand implements Command
{
    private const DIRECTORIES = [
        'src/Controllers',
        'src/Middleware',
        'src/Services',
        'routes',
        'public',
        'tests',
        'config',
        'storage/logs',
        'storage/agents/pids',
    ];

    public function signature(): string
    {
        return 'new project';
    }

    public function description(): string
    {
        return 'Create a new Valhalla API project.';
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $installDependencies = in_array('--install', $arguments, true);
        $arguments = array_values(array_filter($arguments, static fn (string $argument): bool => $argument !== '--install'));
        $name = $arguments[0] ?? 'valhalla-service';
        $target = $this->targetPath($context->workingPath(), $name);

        if (file_exists($target)) {
            $console->error(sprintf('Target already exists: %s', $target));

            return 1;
        }

        foreach (self::DIRECTORIES as $dir) {
            @mkdir($target.DIRECTORY_SEPARATOR.$dir, 0777, true);
        }

        file_put_contents($target.'/composer.json', Templates::projectComposer($this->packageName($name)));
        file_put_contents($target.'/public/index.php', Templates::publicIndex());
        file_put_contents($target.'/routes/api.php', Templates::routesFile());
        file_put_contents($target.'/src/Controllers/HealthController.php', Templates::sampleController());
        file_put_contents($target.'/.env.example', Templates::envExample());
        copy($target.'/.env.example', $target.'/.env');
        file_put_contents($target.'/config/auth.php', Templates::projectAuthConfig());
        file_put_contents($target.'/config/services.php', Templates::projectServicesConfig());
        file_put_contents($target.'/config/logging.php', Templates::projectLoggingConfig());
        file_put_contents($target.'/config/agents.php', Templates::projectAgentsConfig());
        file_put_contents($target.'/.gitignore', Templates::addGitIgnore());
        file_put_contents($target.'/phpunit.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites><testsuite name="Application"><directory>tests</directory></testsuite></testsuites>
</phpunit>
XML);
        file_put_contents($target.'/tests/HealthEndpointTest.php', Templates::projectTest());
        file_put_contents($target.'/README.md', Templates::projectReadme($name));

        $console->line(sprintf('Created project at %s', $target));

        if ($installDependencies) {
            $command = sprintf('cd %s && composer install', escapeshellarg($target));
            passthru($command, $exitCode);

            if ($exitCode !== 0) {
                $console->error('Project created, but composer install failed.');

                return $exitCode;
            }

            $console->line('Dependencies installed.');

            return 0;
        }

        $console->line('Next steps:');
        $console->line(sprintf('  cd %s', basename($target)));
        $console->line('  composer install');
        $console->line('  php -S 127.0.0.1:8080 -t public');

        return 0;
    }

    private function targetPath(string $workingPath, string $name): string
    {
        if (str_starts_with($name, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $name) === 1) {
            return rtrim($name, DIRECTORY_SEPARATOR);
        }

        return rtrim($workingPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
    }

    private function packageName(string $name): string
    {
        $normalized = strtolower(trim(str_replace(['\\', '/', ' '], '-', $name), '-'));

        return 'app/'.$normalized;
    }
}
