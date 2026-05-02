<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class RegexRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['pattern'])) {
            throw new InvalidArgumentException('Regex rule requires a "pattern" parameter.');
        }

        $pattern = $params['pattern'];
        return is_string($value) && preg_match($pattern, $value) === 1;
    }
}
