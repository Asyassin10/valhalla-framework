<?php

namespace Valhalla\Framework\Log;

use InvalidArgumentException;

class LogChannel
{
    private string $name;

    private string $driver;

    private string $path;

    private int $days;

    private string $level;

    protected array $drivers = [
        'stack',
        'single',
        'daily',
    ];

    public function __construct(string $name, array $config = [])
    {
        $driver = $config['driver'] ?? null;
        $level = $config['level'] ?? 'INFO';
        $days = $config['days'] ?? 60;
        $path = $config['path'] ?? storage_path('logs/valhala.log');

        if ($driver === null) {
            throw new InvalidArgumentException('Driver is required.');
        }

        if (! in_array($driver, $this->drivers, true)) {
            throw new InvalidArgumentException(
                "Driver [$driver] is not supported."
            );
        }

        $this->driver = $driver;
        $this->level = $level;
        $this->days = $days;
        $this->name = $name;
        $this->path = $path;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getDays(): string
    {
        return $this->days;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
