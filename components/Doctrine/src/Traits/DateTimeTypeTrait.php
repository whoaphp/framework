<?php

/**
 * Copyright 2015-2019 info@neomerx.com
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

declare (strict_types=1);

namespace Limoncello\Doctrine\Traits;

use DateTimeInterface;
use Doctrine\DBAL\Types\ConversionException;
use Limoncello\Doctrine\Json\DateTime as LimoncelloDateTime;

/**
 * @package Limoncello\Doctrine
 */
trait DateTimeTypeTrait
{
    /**
     * @param        $value
     * @param string $nonJsonFormat
     * @param string $typeName
     *
     * @return DateTimeInterface|null
     * @throws ConversionException
     */
    private function convertToDateTimeFromString(
        $value,
        string $nonJsonFormat,
        string $typeName
    ): ?DateTimeInterface
    {
        if ($value instanceof DateTimeInterface || $value === null) {
            $result = $value;
        } elseif (is_string($value) === true) {
            $result = LimoncelloDateTime::createFromFormat($nonJsonFormat, $value);
            $result = $result !== false ?
                $result : LimoncelloDateTime::createFromFormat(LimoncelloDateTime::JSON_API_FORMAT, $value);
            if ($result === false) {
                throw ConversionException::conversionFailed($value, $typeName);
            }
        } else {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                DateTimeInterface::class,
                [DateTimeInterface::class, LimoncelloDateTime::class, 'string']
            );
        }

        return $result;
    }
}
