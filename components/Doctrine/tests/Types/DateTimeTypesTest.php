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

namespace Limoncello\Tests\Doctrine\Types;

use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Limoncello\Doctrine\Json\Date as LimoncelloDate;
use Limoncello\Doctrine\Json\DateTime as LimoncelloDateTime;
use Limoncello\Doctrine\Types\DateTimeType;
use Limoncello\Doctrine\Types\DateType;
use Limoncello\Tests\Doctrine\TestCase;

/**
 * @package Limoncello\Tests\Doctrine
 */
class DateTimeTypesTest extends TestCase
{
    /**
     * @inheritdoc
     *
     * @throws DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Type::hasType(DateTimeType::NAME) === true ?: Type::addType(DateTimeType::NAME, DateTimeType::class);
        Type::hasType(DateType::NAME) === true ?: Type::addType(DateType::NAME, DateType::class);
    }

    /**
     * Test date time type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeConversion(): void
    {
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = '2001-02-03T04:05:06+0000';

        $this->assertIsString($dateTime);

        /** @var LimoncelloDateTime $phpValue */
        $phpValue = $type->convertToPHPValue($dateTime, $platform);

        $this->assertInstanceOf(LimoncelloDateTime::class, $phpValue);
        $this->assertEquals(981173106, $phpValue->getTimestamp());
        $this->assertEquals($dateTime, $phpValue->jsonSerialize());
    }

    /**
     * Test date time type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeConversion1(): void
    {
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = new DateTime('2001-02-03T04:05:06+0000');

        $this->assertInstanceOf(DateTime::class, $dateTime);

        /** @var LimoncelloDateTime $phpValue */
        $phpValue = $type->convertToPHPValue($dateTime, $platform);

        $this->assertInstanceOf(LimoncelloDateTime::class, $phpValue);
        $this->assertEquals(981173106, $phpValue->getTimestamp());
        $this->assertEquals($dateTime->format(LimoncelloDateTime::JSON_API_FORMAT), $phpValue->jsonSerialize());
    }

    /**
     * Test date time type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeConversion2(): void
    {
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = new LimoncelloDateTime('2001-02-03T04:05:06+0000');

        $this->assertInstanceOf(LimoncelloDateTime::class, $dateTime);

        /** @var LimoncelloDateTime $phpValue */
        $phpValue = $type->convertToPHPValue($dateTime, $platform);

        $this->assertInstanceOf(LimoncelloDateTime::class, $phpValue);
        $this->assertEquals(981173106, $phpValue->getTimestamp());
        $this->assertEquals($dateTime->format(LimoncelloDateTime::JSON_API_FORMAT), $phpValue->jsonSerialize());
    }

    /**
     * Test date time type to database conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeToDatabaseConversion1(): void
    {
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = '2001-02-03T04:05:06+0000';

        $this->assertIsString($dateTime);

        /** @var string $databaseValue */
        $databaseValue = $type->convertToDatabaseValue($dateTime, $platform);

        $this->assertIsString($databaseValue);
        $this->assertEquals('2001-02-03 04:05:06', $databaseValue);
    }

    /**
     * Test date time type to database conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeToDatabaseConversion2(): void
    {
        /** @var DateTimeType $type */
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = new DateTime('2001-02-03 04:05:06');

        $this->assertInstanceOf(DateTime::class, $dateTime);

        /** @var string $phpValue */
        $phpValue = $type->convertToDatabaseValue($dateTime, $platform);
        $this->assertEquals('2001-02-03 04:05:06', $phpValue);
    }

    /**
     * Test date time type to database conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeToDatabaseConversion3(): void
    {
        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = new LimoncelloDateTime('2001-02-03 04:05:06');

        $this->assertInstanceOf(LimoncelloDateTime::class, $dateTime);

        /** @var string $databaseValue */
        $databaseValue = $type->convertToDatabaseValue($dateTime, $platform);

        $this->assertIsString($databaseValue);

        $this->assertEquals('2001-02-03 04:05:06', $databaseValue);
    }

    /**
     * Test date time type to database conversion invalid input.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeToDatabaseConversionInvalidInput1(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);

        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = 'XXX';

        $type->convertToDatabaseValue($date, $platform);
    }

    /**
     * Test date time type to database conversion invalid input.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTimeTypeToDatabaseConversionInvalidInput2(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);

        $type     = Type::getType(DateTimeType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $dateTime = new \stdClass();

        $type->convertToDatabaseValue($dateTime, $platform);
    }

    /**
     * Test date type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeConversion(): void
    {
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = '2001-02-03';

        $this->assertIsString($date);

        /** @var DateTime $phpValue */
        $phpValue = $type->convertToPHPValue($date, $platform);

        $this->assertInstanceOf(LimoncelloDate::class, $phpValue);

        $this->assertEquals(981158400, $phpValue->getTimestamp());
        $this->assertEquals($date, $phpValue->jsonSerialize());
    }

    /**
     * Test date type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeConversion1(): void
    {
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = new DateTime('2001-02-03');

        $this->assertInstanceOf(DateTime::class, $date);

        /** @var DateTime $phpValue */
        $phpValue = $type->convertToPHPValue($date, $platform);

        $this->assertInstanceOf(LimoncelloDate::class, $phpValue);
        $this->assertEquals(981158400, $phpValue->getTimestamp());
        $this->assertEquals($date->format(LimoncelloDate::JSON_API_FORMAT), $phpValue->jsonSerialize());
    }

    /**
     * Test date type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeConversion2(): void
    {
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = new LimoncelloDate('2001-02-03');

        $this->assertInstanceOf(LimoncelloDate::class, $date);

        /** @var DateTime $phpValue */
        $phpValue = $type->convertToPHPValue($date, $platform);

        $this->assertInstanceOf(LimoncelloDate::class, $phpValue);
        $this->assertEquals(981158400, $phpValue->getTimestamp());
        $this->assertEquals($date->format(LimoncelloDate::JSON_API_FORMAT), $phpValue->jsonSerialize());
    }

    /**
     * Test date type to database conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeToDatabaseConversion1(): void
    {
        /** @var DateType $type */
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = '2001-02-03';

        $this->assertIsString($date);

        /** @var string $databaseValue */
        $databaseValue = $type->convertToDatabaseValue($date, $platform);

        $this->assertIsString($databaseValue);
        $this->assertEquals('2001-02-03', $databaseValue);
    }

    /**
     * Test date type to database conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeToDatabaseConversion2(): void
    {
        /** @var DateType $type */
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = new DateTime('2001-02-03');

        $this->assertInstanceOf(DateTime::class, $date);

        /** @var string $databaseValue */
        $databaseValue = $type->convertToDatabaseValue($date, $platform);

        $this->assertIsString($databaseValue);
        $this->assertEquals('2001-02-03', $databaseValue);
    }

    /**
     * Test date type conversion.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeToDatabaseConversion3(): void
    {
        /** @var DateType $type */
        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = new LimoncelloDate('2001-02-03');

        $this->assertInstanceOf(LimoncelloDate::class, $date);

        /** @var string $databaseValue */
        $databaseValue = $type->convertToDatabaseValue($date, $platform);

        $this->assertIsString($databaseValue);
        $this->assertEquals('2001-02-03', $databaseValue);
    }

    /**
     * Test date type to database conversion invalid type.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeToDatabaseConversionInvalidInput1(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);

        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = 'XXX';

        $type->convertToDatabaseValue($date, $platform);
    }

    /**
     * Test date type to database conversion invalid type.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testDateTypeToDatabaseConversionInvalidInput2(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);

        $type     = Type::getType(DateType::NAME);
        $platform = $this->createConnection()->getDatabasePlatform();

        $date = new \stdClass();

        $type->convertToDatabaseValue($date, $platform);
    }

    /**
     * @throws Exception
     */
    public function testDateTimeKeepsTimeZone(): void
    {
        // switch timezone
        $currentTimeZone = date_default_timezone_get();
        date_default_timezone_set('Antarctica/Casey');

        $dateTime     = new DateTime('2001-02-03 04:05:06');
        $jsonDateTime = LimoncelloDateTime::createFromDateTime($dateTime);

        $this->assertEquals('"2001-02-03T04:05:06+0800"', json_encode($jsonDateTime));


        // return timezone back
        date_default_timezone_set($currentTimeZone);
    }
}
