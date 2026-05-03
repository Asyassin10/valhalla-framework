<?php

namespace Valhalla\Framework\Validator\Contracts;

interface IValidator
{
    public static function validate(mixed $value, array $params = []): bool;
}
