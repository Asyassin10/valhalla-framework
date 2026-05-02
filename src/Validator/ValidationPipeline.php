<?php

namespace Valhalla\Framework\Validator;

class ValidationPipeline
{
    public array $rules;
    public mixed $value;

    public function __construct(array $rules, mixed $value)
    {
        $this->rules = $rules;
        $this->value = $value;
    }
}
