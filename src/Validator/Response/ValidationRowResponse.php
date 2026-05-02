<?php

namespace Valhalla\Framework\Validator\Response;

class ValidationRowResponse
{
    public function __construct(
        private bool $isValid,
        private array $errors
    ) {

    }
    public function getIsValid(): bool
    {
        return $this->isValid;
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
}
