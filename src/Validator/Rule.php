<?php

declare(strict_types=1);

namespace Valhalla\Framework\Validator;

final class Rule
{
    public function __construct(
        public readonly string $name,
        public readonly array $rawParams = [],
    ) {}

    public static function fromString(string $rule): self
    {
        if (!str_contains($rule, ':')) {
            return new self($rule);
        }

        [$name, $paramString] = explode(':', $rule, 2);

        return new self($name, explode(',', $paramString));
    }
}
