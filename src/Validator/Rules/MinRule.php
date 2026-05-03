<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class MinRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['min'])) {
            throw new InvalidArgumentException('Min rule requires a "min" parameter.');
        }

        $min = (float) $params['min'];
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }
}
