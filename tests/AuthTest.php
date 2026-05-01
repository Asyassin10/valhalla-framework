<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Auth\AuthManager;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Support\Config;

final class AuthTest extends TestCase
{
    private AuthManager $manager;

    protected function setUp(): void
    {
        $config = new Config(dirname(__DIR__));
        $config->load();
        $this->manager = new AuthManager($config);
        Auth::setManager($this->manager);
    }

    public function test_jwt_round_trip_works(): void
    {
        $token = $this->manager->generateToken(['id' => 7, 'name' => 'Jane']);
        $request = Request::fromArray([
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);

        $user = $this->manager->attempt($request);

        self::assertSame('Jane', $user['name']);
        self::assertTrue($this->manager->check());
    }

    public function test_api_token_works(): void
    {
        $request = Request::fromArray([
            'headers' => ['X-API-Token' => 'local-service-token'],
        ]);

        $user = $this->manager->attempt($request);

        self::assertSame('service.local', $user['id']);
    }
}
