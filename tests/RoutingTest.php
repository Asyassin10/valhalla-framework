<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\Core\Application;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Core\Router;
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Routing\Attributes\Get;
use Valhalla\Framework\Routing\Attributes\Middleware;
use Valhalla\Framework\Routing\Attributes\Post;

final class RoutingTest extends TestCase
{
    private int $applicationCount = 0;

    private int $bufferLevel = 0;

    protected function setUp(): void
    {
        $this->applicationCount = 0;
        $this->bufferLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->bufferLevel) {
            ob_end_clean();
        }

        for ($i = 0; $i < $this->applicationCount; $i++) {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function test_route_facade_registers_and_dispatches_routes(): void
    {
        $app = $this->makeApplication();

        Route::get('/facade', fn () => Response::json(['via' => 'facade']));
        Route::post('/facade', fn () => Response::json(['via' => 'post']));

        $response = $app->handle(Request::fromArray([
            'method' => 'GET',
            'path' => '/facade',
        ]));

        self::assertStringContainsString('"via": "facade"', $response->payload());
        self::assertCount(2, $app->router()->routes());
    }

    public function test_group_supports_router_and_facade_callback_styles(): void
    {
        $router = new Router();

        $router->group('/legacy', [], function (Router $router): void {
            $router->get('/status', fn () => Response::json(['group' => 'legacy']));
        });

        $app = $this->makeApplication();
        $app->singleton('router', fn () => $router);
        Route::group('/facade', [], function (): void {
            Route::get('/status', fn () => Response::json(['group' => 'facade']));
        });

        $legacy = $router->dispatch(Request::fromArray([
            'method' => 'GET',
            'path' => '/legacy/status',
        ]));
        $facade = $router->dispatch(Request::fromArray([
            'method' => 'GET',
            'path' => '/facade/status',
        ]));

        self::assertStringContainsString('"group": "legacy"', $legacy->payload());
        self::assertStringContainsString('"group": "facade"', $facade->payload());
    }

    public function test_mixed_router_and_route_styles_can_load_from_same_file(): void
    {
        $file = sys_get_temp_dir().'/valhalla-routes-'.bin2hex(random_bytes(4)).'.php';
        file_put_contents($file, <<<'PHP'
<?php

use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

$router->get('/legacy', fn () => Response::json(['style' => 'router']));
Route::get('/facade', fn () => Response::json(['style' => 'facade']));
PHP);

        $app = $this->makeApplication();
        $app->loadRoutes($file);

        $legacy = $app->handle(Request::fromArray(['path' => '/legacy']));
        $facade = $app->handle(Request::fromArray(['path' => '/facade']));

        @unlink($file);

        self::assertStringContainsString('"style": "router"', $legacy->payload());
        self::assertStringContainsString('"style": "facade"', $facade->payload());
    }

    public function test_controller_array_handlers_work(): void
    {
        $router = new Router();
        $router->get('/users', [RoutingTestController::class, 'index']);

        $response = $router->dispatch(Request::fromArray(['path' => '/users']));

        self::assertStringContainsString('"message": "controller"', $response->payload());
    }

    public function test_missing_controller_class_throws_runtime_exception(): void
    {
        $router = new Router();
        $router->get('/users', ['MissingController', 'index']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Controller class [MissingController] not found.');

        $router->dispatch(Request::fromArray(['path' => '/users']));
    }

    public function test_missing_controller_method_throws_runtime_exception(): void
    {
        $router = new Router();
        $router->get('/users', [RoutingTestController::class, 'missing']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Method [missing] not found in [%s].', RoutingTestController::class));

        $router->dispatch(Request::fromArray(['path' => '/users']));
    }

    public function test_duplicate_route_registration_throws_runtime_exception(): void
    {
        $router = new Router();
        $router->get('/users', fn () => Response::json(['ok' => true]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Route [GET /users] is already registered.');

        $router->get('/users', fn () => Response::json(['ok' => false]));
    }

    public function test_attribute_routes_can_be_loaded_through_application(): void
    {
        $app = $this->makeApplication();
        $app->loadAttributeRoutes(AttributeRoutingTestController::class);

        $index = $app->handle(Request::fromArray([
            'method' => 'GET',
            'path' => '/attribute-users',
        ]));
        $store = $app->handle(Request::fromArray([
            'method' => 'POST',
            'path' => '/attribute-users',
        ]));

        self::assertStringContainsString('"users": []', $index->payload());
        self::assertStringContainsString('"created": true', $store->payload());
        self::assertSame([AttributeRoutingMiddleware::class], $app->router()->routes()[0]->middleware);
    }

    public function test_attribute_routes_are_loaded_automatically_from_app_controllers(): void
    {
        $basePath = sys_get_temp_dir().'/valhalla-app-'.bin2hex(random_bytes(4));
        mkdir($basePath.'/config', 0777, true);
        mkdir($basePath.'/routes', 0777, true);
        mkdir($basePath.'/src/Controllers', 0777, true);

        file_put_contents($basePath.'/config/logging.php', <<<'PHP'
<?php

declare(strict_types=1);

return [
    'driver' => 'single',
    'channel' => 'application',
    'path' => storage_path('logs'),
    'level' => 'DEBUG',
];
PHP);
        file_put_contents($basePath.'/config/auth.php', <<<'PHP'
<?php

declare(strict_types=1);

return [
    'jwt' => [
        'secret' => 'change-me',
        'issuer' => 'valhalla-service',
        'audience' => 'internal-clients',
        'ttl' => 3600,
        'algo' => 'HS256',
    ],
    'api_tokens' => [],
];
PHP);
        file_put_contents($basePath.'/routes/api.php', <<<'PHP'
<?php

declare(strict_types=1);

use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;

Route::get('/closure-health', fn () => Response::json(['ok' => true]));
PHP);
        file_put_contents($basePath.'/src/Controllers/AutoUsersController.php', <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Routing\Attributes\Get;

final class AutoUsersController
{
    #[Get('/auto-users')]
    public function index(Request $request): Response
    {
        return Response::json([
            'auto' => true,
            'path' => $request->path(),
        ]);
    }
}
PHP);

        $this->applicationCount++;
        $app = new Application($basePath);
        $app->loadRoutes($basePath.'/routes/api.php');

        $closure = $app->handle(Request::fromArray([
            'method' => 'GET',
            'path' => '/closure-health',
        ]));
        $attribute = $app->handle(Request::fromArray([
            'method' => 'GET',
            'path' => '/auto-users',
        ]));

        $this->deleteDirectory($basePath);

        self::assertStringContainsString('"ok": true', $closure->payload());
        self::assertStringContainsString('"auto": true', $attribute->payload());
    }

    public function test_duplicate_attribute_route_registration_throws_runtime_exception(): void
    {
        $app = $this->makeApplication();
        $app->loadAttributeRoutes(AttributeRoutingTestController::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Route [GET /attribute-users] is already registered.');

        $app->loadAttributeRoutes(DuplicateAttributeRoutingTestController::class);
    }

    private function makeApplication(): Application
    {
        $this->applicationCount++;

        return new Application(dirname(__DIR__));
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($path);
    }
}

final class RoutingTestController
{
    public function index(Request $request): Response
    {
        return Response::json([
            'message' => 'controller',
            'path' => $request->path(),
        ]);
    }
}

final class AttributeRoutingTestController
{
    #[Get('/attribute-users')]
    #[Middleware(AttributeRoutingMiddleware::class)]
    public function index(Request $request): Response
    {
        return Response::json([
            'users' => [],
            'path' => $request->path(),
        ]);
    }

    #[Post('/attribute-users')]
    public function store(Request $request): Response
    {
        return Response::json([
            'created' => true,
            'path' => $request->path(),
        ]);
    }
}

final class AttributeRoutingMiddleware implements \Valhalla\Framework\Core\MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        return $next($request);
    }
}

final class DuplicateAttributeRoutingTestController
{
    #[Get('/attribute-users')]
    public function index(Request $request): Response
    {
        return Response::json([
            'duplicate' => true,
            'path' => $request->path(),
        ]);
    }
}
