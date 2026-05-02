<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class BooleanRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        return in_array($value, [true, false, 0, 1, "0", "1"], true);
    }
}
