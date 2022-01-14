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

namespace Whoa\Validation\Rules\Converters;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\ExecuteRule;
use function is_iterable;
use function is_numeric;
use function is_string;

/**
 * @package Whoa\Validation
 */
final class StringArrayToIntArray extends ExecuteRule
{
    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        $reply = null;

        $result = [];
        if (is_iterable($value) === true) {
            foreach ($value as $key => $mightBeString) {
                if (is_string($mightBeString) === true || is_numeric($mightBeString) === true) {
                    $result[$key] = (int)$mightBeString;
                } else {
                    $reply = static::createErrorReply(
                        $context,
                        $mightBeString,
                        ErrorCodes::IS_STRING,
                        Messages::IS_STRING,
                        []
                    );
                    break;
                }
            }
        } else {
            $reply = static::createErrorReply($context, $value, ErrorCodes::IS_ARRAY, Messages::IS_ARRAY, []);
        }

        return $reply !== null ? $reply : static::createSuccessReply($result);
    }
}
