<?php

namespace Valhalla\Framework\Validator\Response;

class ValidationResponse
{
    public function __construct(
        private array $errors,
        private bool $valid,
    ) {}

    public function getIsValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !$this->valid;
    }
}
