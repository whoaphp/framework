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

namespace Whoa\Validation\Rules\Comparisons;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;
use function assert;
use function is_scalar;
use function is_string;
use function strlen;

/**
 * @package Whoa\Validation
 */
final class StringLengthBetween extends BaseTwoValueComparision
{
    /**
     * @param int $min
     * @param int $max
     */
    public function __construct($min, $max)
    {
        assert(is_scalar($min) === true && is_scalar($max) === true);
        parent::__construct(
            $min,
            $max,
            ErrorCodes::STRING_LENGTH_BETWEEN,
            Messages::STRING_LENGTH_BETWEEN,
            [$min, $max]
        );
    }

    /**
     * @inheritdoc
     */
    public static function compare($value, ContextInterface $context): bool
    {
        assert(is_string($value) === true);
        $result =
            is_string($value) === true &&
            static::readLowerValue($context) <= ($length = strlen($value)) &&
            $length <= static::readUpperValue($context);

        return $result;
    }
}
