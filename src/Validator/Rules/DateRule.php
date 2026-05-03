<?php

namespace Valhalla\Framework\Validator\Rules;

use Carbon\Carbon;
use Throwable;
use Valhalla\Framework\Validator\Contracts\IValidator;

class DateRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        try {
            Carbon::parse($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
