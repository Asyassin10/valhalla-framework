<?php

declare(strict_types=1);

namespace Valhalla\Framework\Services;

final class CircuitBreaker
{
    private array $state = [];

    public function __construct(
        private readonly int $threshold = 3,
        private readonly int $cooldown = 10
    ) {
    }

    public function canPass(string $service): bool
    {
        $entry = $this->state[$service] ?? ['failures' => 0, 'opened_at' => null];

        if ($entry['opened_at'] === null) {
            return true;
        }

        if ((time() - $entry['opened_at']) >= $this->cooldown) {
            $this->reset($service);

            return true;
        }

        return false;
    }

    public function recordFailure(string $service): void
    {
        $entry = $this->state[$service] ?? ['failures' => 0, 'opened_at' => null];
        $entry['failures']++;

        if ($entry['failures'] >= $this->threshold) {
            $entry['opened_at'] = time();
        }

        $this->state[$service] = $entry;
    }

    public function recordSuccess(string $service): void
    {
        $this->reset($service);
    }

    private function reset(string $service): void
    {
        $this->state[$service] = ['failures' => 0, 'opened_at' => null];
    }
}
