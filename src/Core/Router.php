<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

use Closure;
use Valhalla\Framework\Core\Exceptions\MethodNotAllowedException;
use Valhalla\Framework\Core\Exceptions\NotFoundException;

final class Router
{
    /** @var RouteDefinition[] */
    private array $routes = [];

    private array $groupStack = [];

    public function get(string $uri, mixed $handler, array $middleware = []): RouteDefinition
    {
        return $this->add('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, mixed $handler, array $middleware = []): RouteDefinition
    {
        return $this->add('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, mixed $handler, array $middleware = []): RouteDefinition
    {
        return $this->add('PUT', $uri, $handler, $middleware);
    }

    public function delete(string $uri, mixed $handler, array $middleware = []): RouteDefinition
    {
        return $this->add('DELETE', $uri, $handler, $middleware);
    }

    public function group(string $prefix, array $middleware, Closure $callback): void
    {
        $this->groupStack[] = ['prefix' => rtrim($prefix, '/'), 'middleware' => $middleware];
        $callback(...($this->callbackUsesRouter($callback) ? [$this] : []));
        array_pop($this->groupStack);
    }

    public function add(string $method, string $uri, mixed $handler, array $middleware = []): RouteDefinition
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'];
            $groupMiddleware = array_merge($groupMiddleware, $group['middleware']);
        }

        $uri = '/'.trim($prefix.'/'.trim($uri, '/'), '/');
        $route = new RouteDefinition(strtoupper($method), $uri === '//' ? '/' : $uri, $handler, array_merge($groupMiddleware, $middleware));
        $this->routes[] = $route;

        return $route;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function dispatch(Request $request): Response
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            $match = $this->matchRoute($route->uri, $request->path());

            if ($match === null) {
                continue;
            }

            if ($route->method !== $request->method()) {
                $allowed[] = $route->method;

                continue;
            }

            $request->setRouteParams($match);

            return $this->runRoute($route, $request);
        }

        if ($allowed !== []) {
            throw new MethodNotAllowedException($request->method(), $request->path());
        }

        throw new NotFoundException($request->path());
    }

    private function runRoute(RouteDefinition $route, Request $request): Response
    {
        $core = function (Request $request) use ($route): Response {
            $response = $this->resolveHandler($route->handler, $request);

            if (! $response instanceof Response) {
                $response = Response::json($response);
            }

            return $response;
        };

        $pipeline = array_reduce(
            array_reverse($route->middleware),
            fn (callable $next, mixed $middleware): callable => function (Request $request) use ($middleware, $next): Response {
                $instance = is_string($middleware) ? new $middleware() : $middleware;

                if (! $instance instanceof MiddlewareInterface) {
                    throw new \RuntimeException('Invalid middleware supplied.');
                }

                return $instance->handle($request, $next);
            },
            $core
        );

        return $pipeline($request);
    }

    private function callbackUsesRouter(Closure $callback): bool
    {
        return (new \ReflectionFunction($callback))->getNumberOfParameters() > 0;
    }

    private function resolveHandler(mixed $handler, Request $request): mixed
    {
        if (is_array($handler) && count($handler) === 2) {
            [$className, $methodName] = $handler;

            if (! is_string($className) || ! class_exists($className)) {
                throw new \RuntimeException(sprintf('Controller class [%s] not found.', is_string($className) ? $className : get_debug_type($className)));
            }

            if (! is_string($methodName) || ! method_exists($className, $methodName)) {
                $method = is_string($methodName) ? $methodName : get_debug_type($methodName);
                throw new \RuntimeException(sprintf('Method [%s] not found in [%s].', $method, $className));
            }

            return (new $className())->$methodName($request);
        }

        if (is_callable($handler)) {
            return call_user_func($handler, $request);
        }

        return null;
    }

    private function matchRoute(string $routeUri, string $requestUri): ?array
    {
        $pattern = preg_replace('#\{([^}/]+)\}#', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^'.$pattern.'$#';

        if (preg_match($pattern, $requestUri, $matches) !== 1) {
            return null;
        }

        return array_filter($matches, static fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);
    }
}
