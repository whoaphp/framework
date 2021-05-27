<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Copyright 2021 info@whoaphp.com
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

namespace Limoncello\Validation\Rules\Converters;

use Limoncello\Validation\Contracts\Errors\ErrorCodes;
use Limoncello\Validation\Contracts\Execution\ContextInterface;
use Limoncello\Validation\I18n\Messages;
use Limoncello\Validation\Rules\ExecuteRule;
use function is_numeric;
use function is_string;

/**
 * @package Limoncello\Validation
 */
final class StringToFloat extends ExecuteRule
{
    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        if (is_string($value) === true &&
            (is_numeric($value) === true || filter_var($value, FILTER_VALIDATE_FLOAT) === true)
        ) {
            $reply = static::createSuccessReply((float)$value);
        } elseif (is_float($value) === true) {
            $reply = static::createSuccessReply($value);
        } else {
            $reply = static::createErrorReply($context, $value, ErrorCodes::IS_FLOAT, Messages::IS_FLOAT, []);
        }

        return $reply;
    }
}
