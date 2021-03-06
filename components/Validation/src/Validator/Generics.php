<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Validation\Validator;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\Generic\AndOperator;
use Whoa\Validation\Rules\Generic\Enum;
use Whoa\Validation\Rules\Generic\Fail;
use Whoa\Validation\Rules\Generic\Filter;
use Whoa\Validation\Rules\Generic\IfOperator;
use Whoa\Validation\Rules\Generic\OrOperator;
use Whoa\Validation\Rules\Generic\Required;
use Whoa\Validation\Rules\Generic\Success;
use Whoa\Validation\Rules\Generic\Value;
use function assert;
use function is_resource;

/**
 * @package Whoa\Validation
 */
trait Generics
{
    /**
     * @param RuleInterface $first
     * @param RuleInterface $second
     *
     * @return RuleInterface
     */
    protected static function andX(RuleInterface $first, RuleInterface $second): RuleInterface
    {
        return new AndOperator($first, $second);
    }

    /**
     * @param RuleInterface $primary
     * @param RuleInterface $secondary
     *
     * @return RuleInterface
     */
    protected static function orX(RuleInterface $primary, RuleInterface $secondary): RuleInterface
    {
        return new OrOperator($primary, $secondary);
    }

    /**
     * @param callable      $condition
     * @param RuleInterface $onTrue
     * @param RuleInterface $onFalse
     * @param array         $settings
     *
     * @return RuleInterface
     */
    protected static function ifX(
        callable $condition,
        RuleInterface $onTrue,
        RuleInterface $onFalse,
        array $settings = []
    ): RuleInterface
    {
        return new IfOperator($condition, $onTrue, $onFalse, $settings);
    }

    /**
     * @return RuleInterface
     */
    protected static function success(): RuleInterface
    {
        return new Success();
    }

    /**
     * @param int    $errorCode
     * @param string $messageTemplate
     * @param array  $messageParams
     *
     * @return RuleInterface
     */
    protected static function fail(
        int $errorCode = ErrorCodes::INVALID_VALUE,
        string $messageTemplate = Messages::INVALID_VALUE,
        array $messageParams = []
    ): RuleInterface
    {
        return new Fail($errorCode, $messageTemplate, $messageParams);
    }

    /**
     * @param mixed $value
     *
     * @return RuleInterface
     */
    protected static function value($value): RuleInterface
    {
        // check the value is not a resource and can be represented as string
        assert(is_resource($value) === false);

        return new Value($value);
    }

    /**
     * @param array              $values
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function enum(array $values, RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new Enum($values) : new AndOperator(static::enum($values), $next);
    }

    /**
     * @param int                $filterId
     * @param mixed              $options
     * @param int                $errorCode
     * @param string             $messageTemplate
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function filter(
        int $filterId,
        $options = null,
        int $errorCode = ErrorCodes::INVALID_VALUE,
        string $messageTemplate = Messages::INVALID_VALUE,
        RuleInterface $next = null
    ): RuleInterface
    {
        $filterRule = new Filter($filterId, $options, $errorCode, $messageTemplate);

        return $next === null ? $filterRule : new AndOperator($filterRule, $next);
    }

    /**
     * @param RuleInterface $rule
     *
     * @return RuleInterface
     */
    protected static function required(RuleInterface $rule = null): RuleInterface
    {
        return $rule === null ? new Required(static::success()) : new Required($rule);
    }
}
