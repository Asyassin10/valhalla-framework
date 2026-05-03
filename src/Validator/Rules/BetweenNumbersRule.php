<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class BetweenNumbersRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (! isset($params['min'], $params['max'])) {
            throw new InvalidArgumentException(
                'BetweenNumbers rule requires "min" and "max" parameters.'
            );
        }

        if (! is_numeric($value)) {
            return false;
        }

        $number = (float) $value;
        $min = (float) $params['min'];
        $max = (float) $params['max'];

        return $number >= $min && $number <= $max;
    }
}
