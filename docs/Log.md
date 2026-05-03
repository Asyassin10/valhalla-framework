# Logging

Valhalla ships a built-in logger accessible via the `Log` static facade. No third-party logging library is required at runtime.

## Configuration

Create or edit `config/logging.php` in your application:

```php
<?php

return [
    'driver'  => 'single',   // 'single' | 'daily' | 'stack'
    'level'   => 'DEBUG',    // minimum level written to the default channel
    'path'    => storage_path('logs'),

    // optional named channels
    'channels' => [
        'payments' => [
            'driver' => 'daily',
            'level'  => 'WARNING',
            'path'   => storage_path('logs/payments'),
            'days'   => 30,          // keep rotated files for 30 days (default: 60)
        ],
        'audit' => [
            'driver' => 'single',
            'level'  => 'INFO',
            'path'   => storage_path('logs/audit'),
        ],
    ],
];
```

### Drivers

| Driver   | Behaviour |
|----------|-----------|
| `single` | One file per channel, never rotated |
| `daily`  | One file per day (`channel-YYYY-MM-DD.log`), files older than `days` are deleted automatically |
| `stack`  | Intended for grouping multiple channels (same write semantics as `single` for the default channel) |

### Levels (lowest → highest)

`DEBUG` → `INFO` → `NOTICE` → `WARNING` → `ERROR` → `CRITICAL` → `ALERT` → `EMERGENCY`

A message is written only when its level is **≥** the channel's configured level. The default `app` channel skips this filter and always writes.

## Basic usage

```php
use Valhalla\Framework\Log\Log;

Log::debug('Cache miss for key user:42');
Log::info('User logged in', ['user_id' => 42]);
Log::notice('Deprecated endpoint called');
Log::warning('Disk usage above 80 %');
Log::error('Payment gateway returned 500');
Log::critical('Database connection lost');
Log::alert('Replication lag exceeded threshold');
Log::emergency('Service is down');
```

Every method accepts any value as `$message` — string, array, or any object — and an optional `$context` array.

### Logging strings

```php
Log::info('Order placed successfully');
```

### Logging arrays

```php
Log::info(['order_id' => 123, 'total' => 49.99]);
```

### Logging objects

The logger uses reflection to extract all properties (including private/protected ones) and serialises them automatically.

```php
class Order
{
    public function __construct(
        private int $id,
        private float $total,
    ) {}
}

Log::info(new Order(123, 49.99));
// writes: {"id":123,"total":49.99}
```

### Log with context

Context is merged into the log record as extra structured data.

```php
Log::error('Payment failed', [
    'user_id'    => 7,
    'gateway'    => 'stripe',
    'error_code' => 'card_declined',
]);
```

### Logging exceptions

`logError` captures exception class, file, line, code, and full stack trace automatically.

```php
try {
    // ...
} catch (\Throwable $e) {
    Log::logError($e, ['user_id' => 7]);
}
```

## Named channels

Switch to any channel defined in `config/logging.php` with `Log::channel(name)`. The channel call returns the logger so you can chain the level method directly.

```php
Log::channel('payments')->warning('Refund delayed', ['order_id' => 99]);
Log::channel('audit')->info('Admin deleted user', ['admin_id' => 1, 'target_id' => 42]);
```

> Referencing a channel name that does not exist in config throws `InvalidArgumentException`.

## Log format

Each line written to a file follows this pattern:

```
[2025-06-01 14:32:05] app.INFO: User logged in
[2025-06-01 14:32:06] payments.WARNING: {"order_id":99}
```

`[timestamp] channel.LEVEL: message`

## Log rotation

When the `daily` driver is used, old log files are pruned on every write call. The retention window is controlled by the `days` key in the channel config (default **60 days**).
