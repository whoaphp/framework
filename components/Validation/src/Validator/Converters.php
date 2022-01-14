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

use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\Rules\Converters\StringArrayToIntArray;
use Whoa\Validation\Rules\Converters\StringToArray;
use Whoa\Validation\Rules\Converters\StringToBool;
use Whoa\Validation\Rules\Converters\StringToDateTime;
use Whoa\Validation\Rules\Converters\StringToFloat;
use Whoa\Validation\Rules\Converters\StringToInt;
use Whoa\Validation\Rules\Generic\AndOperator;

/**
 * @package Whoa\Validation
 */
trait Converters
{
    /**
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringToBool(RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new StringToBool() : new AndOperator(new StringToBool(), $next);
    }

    /**
     * @param string             $format
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringToDateTime(string $format, RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new StringToDateTime($format) : new AndOperator(new StringToDateTime($format), $next);
    }

    /**
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringToFloat(RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new StringToFloat() : new AndOperator(new StringToFloat(), $next);
    }

    /**
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringToInt(RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new StringToInt() : new AndOperator(new StringToInt(), $next);
    }

    /**
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringArrayToIntArray(RuleInterface $next = null): RuleInterface
    {
        return $next === null ? new StringArrayToIntArray() : new AndOperator(new StringArrayToIntArray(), $next);
    }

    /**
     * @param string             $delimiter
     * @param int                $limit
     * @param RuleInterface|null $next
     *
     * @return RuleInterface
     */
    protected static function stringToArray(
        string $delimiter,
        int $limit = PHP_INT_MAX,
        RuleInterface $next = null
    ): RuleInterface
    {
        return $next === null ?
            new StringToArray($delimiter, $limit) : new AndOperator(new StringToArray($delimiter, $limit), $next);
    }
}
