<?php

declare(strict_types=1);

namespace Valhalla\Framework\Validator;

use InvalidArgumentException;
use Valhalla\Framework\Validator\Rules\AfterDateRule;
use Valhalla\Framework\Validator\Rules\AfterOrEqualDateRule;
use Valhalla\Framework\Validator\Rules\ArrayRule;
use Valhalla\Framework\Validator\Rules\BeforeDateRule;
use Valhalla\Framework\Validator\Rules\BeforeOrEqualDateRule;
use Valhalla\Framework\Validator\Rules\BetweenNumbersRule;
use Valhalla\Framework\Validator\Rules\BooleanRule;
use Valhalla\Framework\Validator\Rules\DateRule;
use Valhalla\Framework\Validator\Rules\EmailRule;
use Valhalla\Framework\Validator\Rules\InArrayRule;
use Valhalla\Framework\Validator\Rules\IntegerRule;
use Valhalla\Framework\Validator\Rules\LengthRule;
use Valhalla\Framework\Validator\Rules\MaxRule;
use Valhalla\Framework\Validator\Rules\MinRule;
use Valhalla\Framework\Validator\Rules\NegativeNumberRule;
use Valhalla\Framework\Validator\Rules\NotInArrayRule;
use Valhalla\Framework\Validator\Rules\NumericRule;
use Valhalla\Framework\Validator\Rules\PositiveNumberRule;
use Valhalla\Framework\Validator\Rules\RegexRule;
use Valhalla\Framework\Validator\Rules\RequiredRule;
use Valhalla\Framework\Validator\Rules\StringRule;

final class RuleRegistrar
{
    public const REQUIRED        = 'required';
    public const STRING          = 'string';
    public const EMAIL           = 'email';
    public const INTEGER         = 'integer';
    public const NUMERIC         = 'numeric';
    public const BOOLEAN         = 'boolean';
    public const ARRAY           = 'array';
    public const DATE            = 'date';
    public const POSITIVE        = 'positive';
    public const NEGATIVE        = 'negative';
    public const MIN             = 'min';
    public const MAX             = 'max';
    public const LENGTH          = 'length';
    public const REGEX           = 'regex';
    public const BETWEEN         = 'between';
    public const IN              = 'in';
    public const NOT_IN          = 'not_in';
    public const AFTER           = 'after';
    public const AFTER_OR_EQUAL  = 'after_or_equal';
    public const BEFORE          = 'before';
    public const BEFORE_OR_EQUAL = 'before_or_equal';

    /**
     * Maps rule name => [class, paramKeys, variadic]
     *
     * variadic=true: all positional params are collected into the first param key as an array.
     * e.g. "in:a,b,c" → ['allowed' => ['a', 'b', 'c']]
     */
    private const MAP = [
        self::REQUIRED        => [RequiredRule::class,         [],             false],
        self::STRING          => [StringRule::class,           [],             false],
        self::EMAIL           => [EmailRule::class,            [],             false],
        self::INTEGER         => [IntegerRule::class,          [],             false],
        self::NUMERIC         => [NumericRule::class,          [],             false],
        self::BOOLEAN         => [BooleanRule::class,          [],             false],
        self::ARRAY           => [ArrayRule::class,            [],             false],
        self::DATE            => [DateRule::class,             [],             false],
        self::POSITIVE        => [PositiveNumberRule::class,   [],             false],
        self::NEGATIVE        => [NegativeNumberRule::class,   [],             false],
        self::MIN             => [MinRule::class,              ['min'],        false],
        self::MAX             => [MaxRule::class,              ['max'],        false],
        self::LENGTH          => [LengthRule::class,           ['length'],     false],
        self::REGEX           => [RegexRule::class,            ['pattern'],    false],
        self::BETWEEN         => [BetweenNumbersRule::class,   ['min', 'max'], false],
        self::IN              => [InArrayRule::class,          ['allowed'],    true],
        self::NOT_IN          => [NotInArrayRule::class,       ['forbidden'],  true],
        self::AFTER           => [AfterDateRule::class,        ['date'],       false],
        self::AFTER_OR_EQUAL  => [AfterOrEqualDateRule::class, ['date'],       false],
        self::BEFORE          => [BeforeDateRule::class,       ['date'],       false],
        self::BEFORE_OR_EQUAL => [BeforeOrEqualDateRule::class,['date'],       false],
    ];

    /**
     * @return array{0: class-string<\Valhalla\Framework\Validator\Contracts\IValidator>, 1: array}
     */
    public static function resolve(Rule $rule): array
    {
        if (!array_key_exists($rule->name, self::MAP)) {
            throw new InvalidArgumentException("Unknown validation rule: \"{$rule->name}\".");
        }

        [$class, $paramKeys, $variadic] = self::MAP[$rule->name];

        return [$class, self::mapParams($rule->rawParams, $paramKeys, $variadic)];
    }

    private static function mapParams(array $raw, array $keys, bool $variadic): array
    {
        if (empty($keys)) {
            return [];
        }

        if ($variadic) {
            return [$keys[0] => $raw];
        }

        $params = [];
        foreach ($keys as $i => $key) {
            if (array_key_exists($i, $raw)) {
                $params[$key] = $raw[$i];
            }
        }

        return $params;
    }
}
