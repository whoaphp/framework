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
use function is_string;
use function preg_match;

/**
 * @package Whoa\Validation
 */
final class StringRegExp extends BaseOneValueComparision
{
    /**
     * @param mixed $pattern
     */
    public function __construct($pattern)
    {
        assert(is_string($pattern) === true);
        parent::__construct(
            $pattern,
            ErrorCodes::STRING_REG_EXP,
            Messages::STRING_REG_EXP,
            [$pattern]
        );
    }

    /**
     * @inheritdoc
     */
    public static function compare($value, ContextInterface $context): bool
    {
        assert(is_string($value) === true);
        $result = is_string($value) === true && preg_match(static::readValue($context), $value) === 1;

        return $result;
    }
}
