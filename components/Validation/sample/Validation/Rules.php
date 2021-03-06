<?php

/**
 * Copyright 2015-2019 info@neomerx.com
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

namespace Sample\Validation;

use DateTime;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use function assert;

/**
 * @package Sample
 */
class Rules extends \Whoa\Validation\Rules
{
    /** @var string Message Template */
    const MESSAGE_TEMPLATE_EMAIL = 'The value should be a valid email address.';

    /**
     * @return RuleInterface
     */
    public static function sku(): RuleInterface
    {
        return static::stringToInt(new IsSkuRule());
    }

    /**
     * @param int $max
     *
     * @return RuleInterface
     */
    public static function amount(int $max): RuleInterface
    {
        assert($max > 0);

        return static::stringToInt(static::between(1, $max));
    }

    /**
     * @return RuleInterface
     */
    public static function deliveryDate(): RuleInterface
    {
        return static::stringToDateTime(DateTime::ISO8601, new IsDeliveryDateRule());
    }

    /**
     * @return RuleInterface
     */
    public static function email(): RuleInterface
    {
        return static::isString(
            static::filter(
                FILTER_VALIDATE_EMAIL,
                null,
                Errors::IS_EMAIL,
                static::MESSAGE_TEMPLATE_EMAIL,
                static::stringLengthMax(255)
            )
        );
    }

    /**
     * @return RuleInterface
     */
    public static function address1(): RuleInterface
    {
        return static::isString(static::stringLengthBetween(1, 255));
    }

    /**
     * @return RuleInterface
     */
    public static function address2(): RuleInterface
    {
        return static::nullable(static::isString(static::stringLengthMax(255)));
    }

    /**
     * @return RuleInterface
     */
    public static function areTermsAccepted(): RuleInterface
    {
        return static::stringToBool(static::equals(true));
    }
}
