<?php

namespace Valhalla\Framework\Validator\Rules;

use Carbon\Carbon;
use Throwable;
use Valhalla\Framework\Validator\Contracts\IValidator;

class AfterOrEqualDateRule implements IValidator
{
    public static function validate(mixed $value, array $params = []): bool
    {
        if (!isset($params['date'])) {
            return false;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        try {
            return Carbon::parse($value)
                ->gte(Carbon::parse($params['date']));
        } catch (Throwable) {
            return false;
        }
    }
}
