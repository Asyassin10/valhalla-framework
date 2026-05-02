<?php

declare(strict_types=1);

namespace Valhalla\Framework\ORM;

use RuntimeException;
use Valhalla\Framework\ORM\Contracts\OrmDriverInterface;
use Valhalla\Framework\ORM\Drivers\DoctrineDriver;
use Valhalla\Framework\ORM\Drivers\EloquentDriver;
use Valhalla\Framework\Support\Config;

final class OrmManager
{
    private ?OrmDriverInterface $driver = null;

    private bool $booted = false;

    public function __construct(
        private readonly Config $config,
        private readonly string $basePath
    ) {
    }

    public function installed(): bool
    {
        return is_file($this->basePath.'/config/orm.php')
            && is_string($this->config->get('orm.driver'));
    }

    public function boot(): void
    {
        if (! $this->installed() || $this->booted) {
            return;
        }

        $this->driver()->boot((array) $this->config->get('database', []));
        $this->booted = true;
    }

    public function driver(): OrmDriverInterface
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        if (! $this->installed()) {
            throw new RuntimeException('ORM is not installed. Run [valhalla orm:install eloquent] or [valhalla orm:install doctrine].');
        }

        $driver = (string) $this->config->get('orm.driver');

        $this->driver = match ($driver) {
            'eloquent' => new EloquentDriver($this->basePath),
            'doctrine' => new DoctrineDriver($this->basePath),
            default => throw new RuntimeException(sprintf('Unsupported ORM driver [%s].', $driver)),
        };

        return $this->driver;
    }
}
