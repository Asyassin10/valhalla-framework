<?php

declare(strict_types=1);

namespace Valhalla\Framework\Validator;

use Valhalla\Framework\Validator\Response\ValidationResponse;
use Valhalla\Framework\Validator\Response\ValidationRowResponse;

class Validator
{
    /** @var ValidationRowResponse[] */
    private array $responses = [];

    private function __construct(
        private readonly array $rules,
        private readonly array $data,
    ) {}

    public static function make(array $data, array $rules): ValidationResponse
    {
        return (new self($rules, $data))->validate();
    }

    private function validate(): ValidationResponse
    {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $this->responses[] = $this->validateField($field, $fieldRules, $value);
        }

        return $this->buildResponse();
    }

    private function validateField(string $field, array $fieldRules, mixed $value): ValidationRowResponse
    {
        $errors = [];

        foreach ($fieldRules as $ruleInput) {
            $rule = Rule::fromString($ruleInput);
            [$class, $params] = RuleRegistrar::resolve($rule);

            if (!$class::validate($value, $params)) {
                $errors[$field][] = sprintf(
                    'The %s field failed the %s rule.',
                    $field,
                    $rule->name,
                );
            }
        }

        return new ValidationRowResponse(empty($errors), $errors);
    }

    private function buildResponse(): ValidationResponse
    {
        $errors = [];
        $valid = true;

        foreach ($this->responses as $row) {
            if (!$row->getIsValid()) {
                $valid = false;
            }
            if ($row->getErrors()) {
                $errors[] = $row->getErrors();
            }
        }

        return new ValidationResponse($errors, $valid);
    }
}
