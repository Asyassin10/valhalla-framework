<?php

declare(strict_types=1);

namespace Valhalla\Framework\CLI;

final class Templates
{
    public static function controller(string $namespace, string $class): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class {$class}
{
    public function __invoke(Request \$request): Response
    {
        return Response::json([
            'message' => '{$class} responding from Valhalla.',
        ]);
    }
}
PHP;
    }

    public static function middleware(string $namespace, string $class): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Valhalla\Framework\Core\MiddlewareInterface;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class {$class} implements MiddlewareInterface
{
    public function handle(Request \$request, callable \$next): Response
    {
        return \$next(\$request);
    }
}
PHP;
    }

    public static function service(string $namespace, string $class): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

final class {$class}
{
    public function ping(): array
    {
        return ['status' => 'ok'];
    }
}
PHP;
    }

    public static function routesFile(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Middleware\AuthMiddleware;
use Valhalla\Framework\Core\Response;

$router->get('/health', new HealthController());
$router->get('/token', fn () => Response::json([
    'token' => Auth::generateToken(['id' => 1, 'name' => 'Demo Service']),
]));
$router->get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
PHP;
    }

    public static function publicIndex(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

use Valhalla\Framework\Core\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Application(dirname(__DIR__));
$app->loadRoutes(dirname(__DIR__) . '/routes/api.php');
$app->handle()->send();
PHP;
    }

    public static function sampleController(): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class HealthController
{
    public function __invoke(Request \$request): Response
    {
        return Response::json([
            'service' => 'basic-service',
            'status' => 'ok',
            'path' => \$request->path(),
        ]);
    }
}
PHP;
    }

    public static function projectComposer(string $packageName): string
    {
        return <<<JSON
{
    "name": "{$packageName}",
    "type": "project",
    "require": {
        "php": "^8.2",
        "asyassin10/valhalla-framework": "^1.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.5"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "src/"
        }
    },
    "scripts": {
        "test": "phpunit --colors=always"
    }
}
JSON;
    }

    public static function envExample(): string
    {
        return "APP_ENV=local\nAPP_DEBUG=true\nVALHALLA_JWT_SECRET=change-me\nVALHALLA_API_TOKEN=local-service-token\n";
    }

    public static function projectAuthConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'jwt' => [
        'secret' => env('VALHALLA_JWT_SECRET', 'change-me'),
        'issuer' => 'valhalla-service',
        'audience' => 'internal-clients',
        'ttl' => 3600,
        'algo' => 'HS256',
    ],
    'api_tokens' => [
        env('VALHALLA_API_TOKEN', 'local-service-token') => [
            'id' => 'service.local',
            'name' => 'Local Service',
            'roles' => ['service'],
        ],
    ],
];
PHP;
    }

    public static function projectServicesConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'http' => [
        'timeout' => 3.0,
        'retries' => 2,
        'retry_delay_ms' => 100,
        'circuit_breaker' => [
            'threshold' => 3,
            'cooldown' => 10,
        ],
    ],
];
PHP;
    }

    public static function projectLoggingConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'channel' => 'application',
    'path' => storage_path('logs/application.log'),
    'level' => 'debug',
];
PHP;
    }

    public static function projectAgentsConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'registry' => storage_path('agents/registry.json'),
    'pid_dir' => storage_path('agents/pids'),
    'default_host' => '127.0.0.1',
    'default_port' => 9501,
];
PHP;
    }

    public static function projectTest(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\Core\Application;
use Valhalla\Framework\Core\Request;

final class HealthEndpointTest extends TestCase
{
    public function testHealthRouteReturnsOk(): void
    {
        $app = new Application(dirname(__DIR__));
        $app->loadRoutes(dirname(__DIR__) . '/routes/api.php');

        $response = $app->handle(Request::fromArray([
            'method' => 'GET',
            'path' => '/health',
        ]));

        self::assertSame(200, $response->status());
        self::assertStringContainsString('"status": "ok"', $response->payload());
    }
}
PHP;
    }

    public static function projectReadme(string $name): string
    {
        return <<<MD
# {$name}

This project was scaffolded with Valhalla.

## Run

```bash
composer install
php -S 127.0.0.1:8080 -t public
```

## Test

```bash
composer test
```
MD;
    }

    public static function addGitIgnore(): string
    {
        return <<<'PHP'
    *.log
    .env
    .env.backup
    .env.production
    .phpactor.json
    .phpunit.result.cache
    /.codex
    /.cursor/
    /.idea
    /.nova
    /.phpunit.cache
    /.vscode
    /.zed
    /public/storage
    /storage/*.key
    /storage/pail
    /vendor
    PHP;
    }
}
