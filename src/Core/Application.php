<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Throwable;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Auth\AuthManager;
use Valhalla\Framework\Log\Logger;
use Valhalla\Framework\Support\Config;
use Valhalla\Framework\Support\Env;
use Valhalla\Framework\Support\Paths;

final class Application extends Container
{
    private Config $config;
    private Router $router;
    private ErrorHandler $errors;
    private array $providers = [];

    public function __construct(private readonly string $basePath)
    {
        Facade::setApplication($this);

        Paths::setBasePath($basePath);
        Env::load($basePath);

        $this->config = new Config($basePath);
        $this->config->load();

        $this->bootstrapLogger();

        $this->router = new Router();
        $this->errors = new ErrorHandler((bool) env('APP_DEBUG', false));

        Auth::setManager(new AuthManager($this->config));
    }

    /**
     * Bind the logger as a singleton into the container.
     *
     * Logging is core infrastructure — it must always be available,
     * unconditionally, before any user-land code runs.
     */
    private function bootstrapLogger(): void
    {
        $this->singleton('logger', function (): Logger {
            return new Logger(
                $this->config->get('logging', [])
            );
        });
    }

    /**
     * Register a user-land service provider.
     */
    public function register(string $providerClass): void
    {
        $provider = new $providerClass($this);
        $provider->register();
        $this->providers[] = $provider;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function router(): Router
    {
        return $this->router;
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
