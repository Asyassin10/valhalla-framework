<?php

namespace Valhalla\Framework\Validator\Rules;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Contracts\IValidator;

class InArrayRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['allowed'])) {
            throw new InvalidArgumentException('In rule requires an "allowed" parameter.');
        }

        $allowed = (array) $params['allowed'];
        return in_array($value, $allowed, true);
    }
}
