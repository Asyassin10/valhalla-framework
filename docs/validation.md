# Validation

Valhalla provides a fluent validator via `Valhalla\Framework\Validator\Validator`. Pass your data array and a rules map; get back a `ValidationResponse` you can inspect or return directly.

## Quick start

```php
use Valhalla\Framework\Validator\RuleRegistrar;
use Valhalla\Framework\Validator\Validator;

$data = [
    'email'    => 'user@example.com',
    'age'      => 25,
    'username' => 'alice',
];

$result = Validator::make($data, [
    'email'    => [RuleRegistrar::REQUIRED, RuleRegistrar::EMAIL],
    'age'      => [RuleRegistrar::REQUIRED, RuleRegistrar::INTEGER, 'min:18'],
    'username' => [RuleRegistrar::REQUIRED, RuleRegistrar::STRING],
]);

if ($result->fails()) {
    // handle errors
    $errors = $result->getErrors();
}
```

## ValidationResponse API

`Validator::make()` always returns a `ValidationResponse` with three methods:

| Method | Return | Description |
|--------|--------|-------------|
| `getIsValid()` | `bool` | `true` when all fields pass every rule |
| `fails()` | `bool` | Inverse of `getIsValid()` — convenient for early returns |
| `getErrors()` | `array` | Nested array of error messages keyed by field name |

### Error structure

```php
[
    ['email' => ['The email field failed the email rule.']],
    ['age'   => ['The age field failed the min rule.']],
]
```

Each entry is a field map; multiple rule failures for the same field appear as additional items in that field's array.

## Available rules

### No parameters

| Constant | String form | Validates |
|----------|-------------|-----------|
| `RuleRegistrar::REQUIRED` | `'required'` | Value is present and not `null` |
| `RuleRegistrar::STRING` | `'string'` | Value is a string |
| `RuleRegistrar::EMAIL` | `'email'` | Valid e-mail address format |
| `RuleRegistrar::INTEGER` | `'integer'` | Value is an integer |
| `RuleRegistrar::NUMERIC` | `'numeric'` | Value is numeric (int or float) |
| `RuleRegistrar::BOOLEAN` | `'boolean'` | Value is a boolean |
| `RuleRegistrar::ARRAY` | `'array'` | Value is an array |
| `RuleRegistrar::DATE` | `'date'` | Value is a parseable date string |
| `RuleRegistrar::POSITIVE` | `'positive'` | Number is > 0 |
| `RuleRegistrar::NEGATIVE` | `'negative'` | Number is < 0 |

### With parameters

Rules with parameters use the `rule:param` string syntax or pass the raw string directly.

| Constant | String form | Parameter | Validates |
|----------|-------------|-----------|-----------|
| `RuleRegistrar::MIN` | `'min:N'` | `N` – minimum value | Number ≥ N |
| `RuleRegistrar::MAX` | `'max:N'` | `N` – maximum value | Number ≤ N |
| `RuleRegistrar::LENGTH` | `'length:N'` | `N` – exact length | String length === N |
| `RuleRegistrar::REGEX` | `'regex:pattern'` | PCRE pattern | String matches pattern |
| `RuleRegistrar::BETWEEN` | `'between:min,max'` | `min`, `max` | Number is between min and max (inclusive) |
| `RuleRegistrar::IN` | `'in:a,b,c'` | Comma-separated allowed values | Value is one of the listed values |
| `RuleRegistrar::NOT_IN` | `'not_in:a,b,c'` | Comma-separated forbidden values | Value is not one of the listed values |
| `RuleRegistrar::AFTER` | `'after:YYYY-MM-DD'` | Date string | Date is strictly after the given date |
| `RuleRegistrar::AFTER_OR_EQUAL` | `'after_or_equal:YYYY-MM-DD'` | Date string | Date is on or after the given date |
| `RuleRegistrar::BEFORE` | `'before:YYYY-MM-DD'` | Date string | Date is strictly before the given date |
| `RuleRegistrar::BEFORE_OR_EQUAL` | `'before_or_equal:YYYY-MM-DD'` | Date string | Date is on or before the given date |

## Usage examples

### Required + type

```php
$result = Validator::make($data, [
    'name'  => [RuleRegistrar::REQUIRED, RuleRegistrar::STRING],
    'score' => [RuleRegistrar::REQUIRED, RuleRegistrar::NUMERIC],
]);
```

### Numeric range

```php
$result = Validator::make($data, [
    'age'        => ['min:0', 'max:120'],
    'percentage' => ['between:0,100'],
]);
```

### String rules

```php
$result = Validator::make($data, [
    'pin'      => ['length:4'],
    'username' => ['regex:/^[a-z0-9_]+$/'],
]);
```

### Enum-style allow/deny lists

```php
$result = Validator::make($data, [
    'status' => ['in:active,inactive,pending'],
    'role'   => ['not_in:superadmin,root'],
]);
```

### Date comparisons

```php
$result = Validator::make($data, [
    'start_date' => [RuleRegistrar::DATE, 'after:2024-01-01'],
    'end_date'   => [RuleRegistrar::DATE, 'before_or_equal:2025-12-31'],
]);
```

### In a controller

```php
use Valhalla\Framework\Core\Request;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Validator\RuleRegistrar;
use Valhalla\Framework\Validator\Validator;

public function store(Request $request): Response
{
    $result = Validator::make($request->body(), [
        'email'    => [RuleRegistrar::REQUIRED, RuleRegistrar::EMAIL],
        'password' => [RuleRegistrar::REQUIRED, RuleRegistrar::STRING, 'min:8'],
    ]);

    if ($result->fails()) {
        return Response::json(['errors' => $result->getErrors()], 422);
    }

    // proceed with valid data ...
}
```

## Notes

- A field missing from `$data` is treated as `null`; pair `REQUIRED` with other rules to catch absent fields.
- Unknown rule names throw `InvalidArgumentException` at validation time.
- Rules are evaluated in the order declared; all failing rules are collected before returning.
