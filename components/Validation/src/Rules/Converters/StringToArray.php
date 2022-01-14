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
use function assert;
use function explode;
use function is_string;
use function strlen;

/**
 * @package Whoa\Validation
 */
final class StringToArray extends ExecuteRule
{
    /** @var int Property key */
    const PROPERTY_DELIMITER = self::PROPERTY_LAST + 1;

    /** @var int Property key */
    const PROPERTY_LIMIT = self::PROPERTY_DELIMITER + 1;

    /**
     * @param string $delimiter
     * @param int    $limit
     */
    public function __construct(string $delimiter, int $limit = PHP_INT_MAX)
    {
        assert(strlen($delimiter) > 0);
        assert($limit >= 0);

        parent::__construct([
            self::PROPERTY_DELIMITER => $delimiter,
            self::PROPERTY_LIMIT     => $limit,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        $reply = null;

        $result = [];
        if (is_string($value) === true) {
            $properties = $context->getProperties();
            $delimiter  = $properties->getProperty(static::PROPERTY_DELIMITER);
            $limit      = $properties->getProperty(static::PROPERTY_LIMIT);
            $result     = explode($delimiter, $value, $limit);
        } else {
            $reply = static::createErrorReply($context, $value, ErrorCodes::IS_STRING, Messages::IS_STRING, []);
        }

        return $reply !== null ? $reply : static::createSuccessReply($result);
    }
}
