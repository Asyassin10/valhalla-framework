<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Throwable;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Auth\AuthManager;
use Valhalla\Framework\Support\Config;
use Valhalla\Framework\Support\Env;
use Valhalla\Framework\Support\Logger;
use Valhalla\Framework\Support\Paths;

final class Application
{
    private Config $config;

    private Router $router;

    private Logger $logger;

    private ErrorHandler $errors;

    public function __construct(private readonly string $basePath)
    {
        Paths::setBasePath($basePath);
        Env::load($basePath);

        $this->config = new Config($basePath);
        $this->config->load();
        $this->router = new Router();
        $this->logger = new Logger($this->config);
        $this->errors = new ErrorHandler($this->logger, (bool) env('APP_DEBUG', false));

        Auth::setManager(new AuthManager($this->config));
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function loadRoutes(string $path): void
    {
        $router = $this->router;
        require $path;
    }

    public function handle(?Request $request = null): Response
    {
        $request ??= Request::capture();

        try {
            return $this->router->dispatch($request);
        } catch (Throwable $throwable) {
            return $this->errors->render($throwable);
        }
    }
}
