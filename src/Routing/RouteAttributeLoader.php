<?php

declare(strict_types=1);

namespace Valhalla\Framework\Routing;

use ReflectionClass;
use ReflectionMethod;
use Valhalla\Framework\Core\Router;
use Valhalla\Framework\Routing\Attributes\Delete;
use Valhalla\Framework\Routing\Attributes\Get;
use Valhalla\Framework\Routing\Attributes\Middleware;
use Valhalla\Framework\Routing\Attributes\Post;
use Valhalla\Framework\Routing\Attributes\Put;

final class RouteAttributeLoader
{
    public function __construct(private readonly Router $router)
    {
    }

    public function loadFromClass(string $className): void
    {
        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $middleware = $this->middlewareFor($method);

            foreach ($this->routeDefinitionsFor($method) as $route) {
                $this->router->add(
                    $route['method'],
                    $route['uri'],
                    [$className, $method->getName()],
                    $middleware
                );
            }
        }
    }

    private function middlewareFor(ReflectionMethod $method): array
    {
        $middleware = [];

        foreach ($method->getAttributes(Middleware::class) as $attribute) {
            $instance = $attribute->newInstance();
            $middleware = array_merge($middleware, $instance->middleware);
        }

        return $middleware;
    }

    private function routeDefinitionsFor(ReflectionMethod $method): array
    {
        $routes = [];
        $map = [
            Get::class => 'GET',
            Post::class => 'POST',
            Put::class => 'PUT',
            Delete::class => 'DELETE',
        ];

        foreach ($map as $attributeClass => $httpMethod) {
            foreach ($method->getAttributes($attributeClass) as $attribute) {
                $instance = $attribute->newInstance();
                $routes[] = [
                    'method' => $httpMethod,
                    'uri' => $instance->uri,
                ];
            }
        }

        return $routes;
    }
}
