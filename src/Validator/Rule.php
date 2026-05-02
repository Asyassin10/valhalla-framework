<?php

namespace Valhalla\Framework\Validator;

class Rule
{
    public static function required(mixed $value): bool
    {
        return ! is_null($value) && $value !== '';
    }

    public static function string(mixed $value): bool
    {
        return is_string($value);
    }
    public static function email(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }
}
