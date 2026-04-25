<?php

declare(strict_types=1);

namespace Valhalla\Framework\Auth;

use Valhalla\Framework\Core\Exceptions\AuthenticationException;

final class JwtCodec
{
    public function encode(array $payload, string $secret, string $algo = 'HS256'): string
    {
        if ($algo !== 'HS256') {
            throw new \InvalidArgumentException('Only HS256 is supported in Valhalla v1.');
        }

        $header = ['typ' => 'JWT', 'alg' => $algo];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES) ?: '{}'),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}'),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function decode(string $token, string $secret, string $algo = 'HS256'): array
    {
        if ($algo !== 'HS256') {
            throw new \InvalidArgumentException('Only HS256 is supported in Valhalla v1.');
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new AuthenticationException('Invalid JWT token.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!is_array($header) || !is_array($payload) || ($header['alg'] ?? null) !== $algo) {
            throw new AuthenticationException('Invalid JWT token.');
        }

        $expected = $this->base64UrlEncode(hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true));

        if (!hash_equals($expected, $encodedSignature)) {
            throw new AuthenticationException('Invalid JWT token.');
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            throw new AuthenticationException('JWT token has expired.');
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;

        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
