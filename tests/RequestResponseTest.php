<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;

final class RequestResponseTest extends TestCase
{
    public function test_request_from_array_exposes_input_and_headers(): void
    {
        $request = Request::fromArray([
            'method' => 'post',
            'path' => '/users/5',
            'headers' => ['X-Test' => 'yes'],
            'query' => ['page' => 2],
            'body' => ['name' => 'Jane'],
        ]);
        $request->setRouteParams(['id' => '5']);

        self::assertSame('POST', $request->method());
        self::assertSame('/users/5', $request->path());
        self::assertSame('yes', $request->header('x-test'));
        self::assertSame(2, $request->query('page'));
        self::assertSame('Jane', $request->input('name'));
        self::assertSame('5', $request->route('id'));
    }

    public function test_response_payload_is_json(): void
    {
        $response = Response::json(['ok' => true], 201);

        self::assertSame(201, $response->status());
        self::assertSame(['Content-Type' => 'application/json'], $response->headers());
        self::assertStringContainsString('"ok": true', $response->payload());
    }
}
