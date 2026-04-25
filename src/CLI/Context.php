<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI;

use Valhalla\Framework\Support\Config;
use Valhalla\Framework\Support\Env;
use Valhalla\Framework\Support\Paths;

final class Context
{
    private Config $config;

    private string $workingPath;

    public function __construct(private readonly string $installPath)
    {
        $this->workingPath = getcwd() ?: $installPath;
        Paths::setBasePath($this->workingPath);
        Env::load($this->workingPath);
        $this->config = new Config($this->workingPath);
        $this->config->load();
    }

    public function installPath(): string
    {
        return $this->installPath;
    }

    public function workingPath(): string
    {
        return $this->workingPath;
    }

    public function usePath(string $path): void
    {
        $this->workingPath = rtrim($path, DIRECTORY_SEPARATOR);
        Paths::setBasePath($this->workingPath);
        Env::load($this->workingPath);
        $this->config = new Config($this->workingPath);
        $this->config->load();
    }

    public function config(): Config
    {
        return $this->config;
    }
}
