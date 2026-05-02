<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class NumericRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        return is_numeric($value);
    }
}
