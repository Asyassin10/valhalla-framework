<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI\Commands;

use Valhalla\Framework\CLI\Command;
use Valhalla\Framework\CLI\Console;
use Valhalla\Framework\CLI\Context;
use Valhalla\Framework\CLI\Support\CommandSupport;

final class RuntimeCommand implements Command
{
    public function __construct(
        private readonly string $name,
        private readonly string $descriptionText
    ) {
    }

    public function signature(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->descriptionText;
    }

    public function handle(array $arguments, Console $console, Context $context): int
    {
        $runtime = $context->config()->get('container.runtime');

        if (! is_string($runtime) || $runtime === '') {
            $console->error('No container runtime is configured. Run [valhalla install:docker] or [valhalla install:podman] first.');

            return 1;
        }

        $binary = $runtime === 'docker' ? 'docker' : 'podman-compose';
        if (! CommandSupport::binaryExists($binary)) {
            $console->error(sprintf('The configured runtime binary [%s] is not available.', $binary));

            return 1;
        }

        $command = $this->runtimeCommand($runtime);
        if ($command === null) {
            $console->error(sprintf('Unsupported runtime action [%s].', $this->name));

            return 1;
        }

        return CommandSupport::run($command, $context->workingPath());
    }

    private function runtimeCommand(string $runtime): ?string
    {
        $composeFile = $runtime === 'docker' ? 'docker/docker-compose.yml' : 'docker/podman-compose.yml';
        $composeBinary = $runtime === 'docker' ? 'docker compose' : 'podman-compose';

        return match ($this->name) {
            'up' => sprintf('%s -f %s up -d', $composeBinary, escapeshellarg($composeFile)),
            'down' => sprintf('%s -f %s down', $composeBinary, escapeshellarg($composeFile)),
            'build' => sprintf('%s -f %s build', $composeBinary, escapeshellarg($composeFile)),
            'logs' => sprintf('%s -f %s logs -f', $composeBinary, escapeshellarg($composeFile)),
            'shell' => sprintf('%s -f %s exec app sh', $composeBinary, escapeshellarg($composeFile)),
            default => null,
        };
    }
}
