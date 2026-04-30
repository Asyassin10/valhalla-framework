<?php

declare(strict_types=1);

namespace Valhalla\Framework\Auth;

use Valhalla\Framework\Core\Exceptions\AuthenticationException;
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Support\Config;

final class AuthManager
{
    private ?array $currentUser = null;

    private JwtCodec $codec;

    public function __construct(private readonly Config $config)
    {
        $this->codec = new JwtCodec;
    }

    public function attempt(Request $request): ?array
    {
        $token = $request->bearerToken();

        if ($token !== null) {
            $this->currentUser = $this->verifyJwt($token);

            return $this->currentUser;
        }

        $apiToken = (string) $request->header('X-API-Token', '');

        if ($apiToken !== '') {
            $this->currentUser = $this->verifyApiToken($apiToken);

            return $this->currentUser;
        }

        return null;
    }

    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    public function user(): ?array
    {
        return $this->currentUser;
    }

    public function generateToken(array $user): string
    {
        $now = time();
        $ttl = (int) $this->config->get('auth.jwt.ttl', 3600);
        $payload = [
            'iss' => $this->config->get('auth.jwt.issuer', 'valhalla'),
            'aud' => $this->config->get('auth.jwt.audience', 'valhalla-services'),
            'iat' => $now,
            'exp' => $now + $ttl,
            'sub' => $user['id'] ?? $user['email'] ?? 'anonymous',
            'user' => $user,
        ];

        return $this->codec->encode(
            $payload,
            (string) $this->config->get('auth.jwt.secret', 'change-me'),
            (string) $this->config->get('auth.jwt.algo', 'HS256')
        );
    }

    public function verifyJwt(string $token): array
    {
        try {
            $decoded = $this->codec->decode(
                $token,
                (string) $this->config->get('auth.jwt.secret', 'change-me'),
                (string) $this->config->get('auth.jwt.algo', 'HS256')
            );
        } catch (\Throwable $throwable) {
            throw $throwable instanceof AuthenticationException
                ? $throwable
                : new AuthenticationException('Invalid JWT token.');
        }

        return (array) ($decoded['user'] ?? []);
    }

    public function verifyApiToken(string $token): array
    {
        $tokens = (array) $this->config->get('auth.api_tokens', []);

        if (! array_key_exists($token, $tokens)) {
            throw new AuthenticationException('Invalid API token.');
        }

        return (array) $tokens[$token];
    }
}
