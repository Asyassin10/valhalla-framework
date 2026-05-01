<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

final class Request
{
    private array $routeParams = [];

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $headers = [],
        private readonly array $query = [],
        private readonly array|string|null $body = null
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $query = $_GET ?? [];
        $rawBody = file_get_contents('php://input') ?: null;
        $decoded = null;

        if (is_string($rawBody) && $rawBody !== '') {
            $json = json_decode($rawBody, true);
            $decoded = json_last_error() === JSON_ERROR_NONE ? $json : $rawBody;
        }

        return new self($method, $path, $headers, $query, $decoded);
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            strtoupper((string) ($payload['method'] ?? 'GET')),
            (string) ($payload['path'] ?? '/'),
            $payload['headers'] ?? [],
            $payload['query'] ?? [],
            $payload['body'] ?? null
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        foreach ($this->headers as $header => $value) {
            if (strcasecmp($header, $key) === 0) {
                return $value;
            }
        }

        return $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function body(): array|string|null
    {
        return $this->body;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }

        if (! is_array($this->body)) {
            return $default;
        }

        return $this->body[$key] ?? $default;
    }

    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function route(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->routeParams;
        }

        return $this->routeParams[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = (string) $this->header('Authorization', '');

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }
}
