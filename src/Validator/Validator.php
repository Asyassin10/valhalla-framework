<?php

declare(strict_types=1);

namespace Valhalla\Framework\Validator;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Response\ValidationResponse;
use Valhalla\Framework\Validator\Response\ValidationRowResponse;

class Validator
{
    /**
     * @var ValidationRowResponse[]
     */
    private array $responses;
    private ValidationResponse $validationResponse;


    private function __construct(
        private array $rules,
        private array $data
    ) {
    }

    public static function make(array $data, array $rules): ValidationResponse
    {
        return (new self($rules, $data))->validate();
    }
    public function validate(): ValidationResponse
    {
        foreach ($this->rules as $field => $rules_row) {
            $pipe_valid = $this->validatePipeline($field, $rules_row, $this->data[$field]);
            $this->responses[] = $pipe_valid;
        }
        $this->validationResponse =  $this->processResponse();
        return $this->validationResponse;
    }
    public function validatePipeline(mixed $field, array $rules, mixed $value): ValidationRowResponse
    {
        $errors = [];
        $valid = true;
        foreach ($rules as $rule) {
            $res = match ($rule) {
                'required' => Rule::required($value),
                'string' => Rule::string($value),
                'email' => Rule::email((string) $value),
                default => throw new InvalidArgumentException("Unknown rule: {$rule}")
            };
            if (!$res) {
                $valid = false;
                $errors[$field][] = sprintf(
                    'The %s field failed the %s rule and value %s.',
                    $field,
                    $rule,
                    $value
                );
            }
        }

        return new ValidationRowResponse($valid, $errors);

    }





    private function processResponse(): ValidationResponse
    {
        $valid = true;
        $errors = [];
        foreach ($this->responses as $response) {
            if ($response->getIsValid() == false) {
                $valid = false;
            }
            $errors[] = $response->getErrors();
        }
        return new ValidationResponse($errors, $valid);
    }
}
