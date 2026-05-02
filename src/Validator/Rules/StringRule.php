<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class StringRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        return is_string($value);
    }
}
