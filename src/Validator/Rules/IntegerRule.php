<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class IntegerRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
