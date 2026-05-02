<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class LengthRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['length'])) {
            throw new InvalidArgumentException('Length rule requires a "length" parameter.');
        }

        $length = (int) $params['length'];

        return is_string($value) && mb_strlen($value) === $length;
    }
}
