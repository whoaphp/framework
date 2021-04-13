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

declare(strict_types=1);

namespace Limoncello\Doctrine\Types;

use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Limoncello\Doctrine\Json\DateTime;
use Limoncello\Doctrine\Traits\DateTimeTypeTrait;

/**
 * @package Limoncello\Doctrine
 */
class DateTimeType extends \Doctrine\DBAL\Types\DateTimeType
{
    use DateTimeTypeTrait;

    /** @var string Type name */
    const NAME = 'limoncelloDateTime';

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $result = null;

        if ($value !== null && ($dateTimeOrNull = parent::convertToPHPValue($value, $platform)) !== null) {
            assert($dateTimeOrNull instanceof DateTimeInterface);
            // despite the name it's not null already
            $result = DateTime::createFromDateTime($dateTimeOrNull);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValue(
            $this->convertToDateTimeFromString($value, $platform->getDateTimeFormatString(), static::NAME),
            $platform
        );
    }
}