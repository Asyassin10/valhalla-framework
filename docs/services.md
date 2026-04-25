# Service-to-Service Calls

Valhalla wraps Guzzle for JSON-first service calls and includes retry plus circuit-breaker behavior.

```php
use Valhalla\Framework\Services\ServiceClient;

$client = new ServiceClient($app->config());

$result = $client->json(
    'billing',
    'POST',
    'https://billing.internal/payments',
    ['invoice_id' => 99]
);
```

Config lives in `config/services.php`:

```php
'http' => [
    'timeout' => 3.0,
    'retries' => 2,
    'retry_delay_ms' => 100,
    'circuit_breaker' => [
        'threshold' => 3,
        'cooldown' => 10,
    ],
],
```

Future event-driven integrations can layer on top of the same service and config conventions.
