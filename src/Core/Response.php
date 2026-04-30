<?php

declare(strict_types=1);

namespace Valhalla\Framework\Core;

final class Response
{
    public function __construct(
        private readonly mixed $data,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'application/json']
    ) {
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        return new self($data, $status, array_merge(['Content-Type' => 'application/json'], $headers));
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function payload(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        echo $this->payload();
    }
}
