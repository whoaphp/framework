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

namespace Whoa\Validation\Rules\Generic;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\ExecuteRule;
use function assert;
use function in_array;

/**
 * @package Whoa\Validation
 */
final class Enum extends ExecuteRule
{
    /** @var int Property key */
    private const PROPERTY_VALUES = self::PROPERTY_LAST + 1;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        assert(!empty($values));

        parent::__construct([
            static::PROPERTY_VALUES => $values,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        $values = $context->getProperties()->getProperty(static::PROPERTY_VALUES);
        $isOk   = in_array($value, $values);

        return $isOk === true ?
            static::createSuccessReply($value) :
            static::createErrorReply($context, $value, ErrorCodes::INVALID_VALUE, Messages::INVALID_VALUE, []);
    }
}
