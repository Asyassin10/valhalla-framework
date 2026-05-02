<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class ArrayRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        return is_array($value);
    }
}
