<?php

declare(strict_types=1);

namespace Valhalla\Framework\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Valhalla\Framework\Support\Config;

final class ServiceClient
{
    private Client $client;

    private CircuitBreaker $circuitBreaker;

    private int $retries;

    private int $retryDelayMs;

    public function __construct(?Config $config = null, ?Client $client = null, ?CircuitBreaker $circuitBreaker = null)
    {
        $timeout = (float) ($config?->get('services.http.timeout', 3.0) ?? 3.0);
        $threshold = (int) ($config?->get('services.http.circuit_breaker.threshold', 3) ?? 3);
        $cooldown = (int) ($config?->get('services.http.circuit_breaker.cooldown', 10) ?? 10);

        $this->retries = (int) ($config?->get('services.http.retries', 2) ?? 2);
        $this->retryDelayMs = (int) ($config?->get('services.http.retry_delay_ms', 100) ?? 100);
        $this->client = $client ?? new Client(['timeout' => $timeout]);
        $this->circuitBreaker = $circuitBreaker ?? new CircuitBreaker($threshold, $cooldown);
    }

    public function json(string $service, string $method, string $url, array $payload = [], array $headers = []): array
    {
        if (!$this->circuitBreaker->canPass($service)) {
            throw new RuntimeException(sprintf('Circuit breaker is open for [%s].', $service));
        }

        $attempt = 0;

        do {
            try {
                $response = $this->client->request($method, $url, [
                    'headers' => array_merge(['Accept' => 'application/json'], $headers),
                    'json' => $payload,
                ]);

                $this->circuitBreaker->recordSuccess($service);
                $body = (string) $response->getBody();
                $decoded = json_decode($body, true);

                return is_array($decoded) ? $decoded : ['raw' => $body];
            } catch (GuzzleException $exception) {
                $attempt++;
                $this->circuitBreaker->recordFailure($service);

                if ($attempt > $this->retries) {
                    throw new RuntimeException(sprintf('Service call failed for [%s]: %s', $service, $exception->getMessage()), 0, $exception);
                }

                usleep($this->retryDelayMs * 1000);
            }
        } while (true);
    }
}
