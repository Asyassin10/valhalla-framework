<?php

namespace Valhalla\Framework\Validator\Rules;

use Valhalla\Framework\Validator\Contracts\IValidator;

class EmailRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        $email = (string) $value;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }
}
