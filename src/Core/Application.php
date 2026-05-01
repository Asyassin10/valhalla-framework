<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Auth\AuthManager;
use Valhalla\Framework\Log\Logger;
use Valhalla\Framework\ORM\OrmManager;
use Valhalla\Framework\Routing\RouteAttributeLoader;
use Valhalla\Framework\Support\Config;
use Valhalla\Framework\Support\Env;
use Valhalla\Framework\Support\Paths;

final class Application extends Container
{
    private Config $config;

    private Router $router;

    private ErrorHandler $errors;

    private array $providers = [];

    private ExceptionPipeline $pipeline;

    public function __construct(private readonly string $basePath)
    {
        ob_start();
        try {
            $this->safeBoot($basePath);
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            ob_clean();
            echo json_encode([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => $e::class,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ],
            ]);
            exit;
        }
    }

    private function safeBoot(string $basePath): void
    {
        $this->pipeline = new ExceptionPipeline();
        Facade::setApplication($this);

        Paths::setBasePath($basePath);
        Env::load($basePath);

        $this->config = new Config($basePath);
        $this->config->load();

        $this->bootstrapLogger();

        $this->router = new Router();
        $this->singleton('router', fn () => $this->router);
        $this->errors = new ErrorHandler(
            $this->make('logger'),
            (bool) env('APP_DEBUG', false)
        );

        $this->pipeline->add(function (Throwable $e): void {
            $this->make('logger')->logError($e);
        });
        $this->pipeline->add(function (Throwable $e): void {
            ob_clean();
            $this->errors->render($e)->send();
            exit;
        });

        $this->registerErrorHandling();

        Auth::setManager(new AuthManager($this->config));

        if (is_file($basePath.'/config/orm.php')) {
            (new OrmManager($this->config, $basePath))->boot();
        }
    }

    private function registerErrorHandling(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e): void {
            $this->pipeline->handle($e);
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();

            if (! $error || ! in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR], true)) {
                return;
            }

            $e = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $this->pipeline->handle($e);
        });
    }

    private function bootstrapLogger(): void
    {
        $this->singleton('logger', function (): Logger {
            return new Logger(
                $this->config->get('logging', [])
            );
        });
    }

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
        /** @var Router $router */
        $router = $this->router;
        require $path;
        $this->loadAttributeRoutesFromDirectory(
            $this->basePath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Controllers',
            'App\\Controllers'
        );
    }

    public function loadAttributeRoutes(string $controllerClass): void
    {
        $loader = new RouteAttributeLoader($this->router);
        $loader->loadFromClass($controllerClass);
    }

    private function loadAttributeRoutesFromDirectory(string $directory, string $namespace): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->classNameFromFile($file->getPathname(), $directory, $namespace);
            require_once $file->getPathname();

            if (! class_exists($className)) {
                continue;
            }

            $this->loadAttributeRoutes($className);
        }
    }

    private function classNameFromFile(string $path, string $directory, string $namespace): string
    {
        $relativePath = substr($path, strlen(rtrim($directory, DIRECTORY_SEPARATOR)) + 1);
        $relativeClass = str_replace(
            [DIRECTORY_SEPARATOR, '.php'],
            ['\\', ''],
            $relativePath
        );

        return $namespace.'\\'.$relativeClass;
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
