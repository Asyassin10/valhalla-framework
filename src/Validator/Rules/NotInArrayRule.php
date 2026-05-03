<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class NotInArrayRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['forbidden'])) {
            throw new InvalidArgumentException('NotIn rule requires a "forbidden" parameter.');
        }

        $forbidden = (array) $params['forbidden'];
        return !in_array($value, $forbidden, true);
    }
}
