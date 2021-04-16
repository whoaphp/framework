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

namespace Limoncello\Tests\Passport\Adaptors;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Limoncello\Passport\Adaptors\MySql\DatabaseSchemaMigrationTrait;
use Limoncello\Passport\Entities\DatabaseSchema;
use Limoncello\Tests\Passport\Data\User;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

/**
 * @package Limoncello\Tests\Passport
 */
class DatabaseSchemaMigrationTest extends TestCase
{
    use DatabaseSchemaMigrationTrait;

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test migration fail.
     */
    public function testMigrationFail()
    {
        $this->expectException(DBALException::class);

        /** @var Mock $connection */
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getSchemaManager')->zeroOrMoreTimes()->withNoArgs()->andReturnSelf();
        $connection->shouldReceive('dropAndCreateTable')->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();
//        $connection->shouldReceive('isConnected')->once()->withNoArgs()->andReturn(true);
        $connection->shouldReceive('isConnected')->times(2)->withNoArgs()->andReturn(true);
        $connection->shouldReceive('tablesExist')->zeroOrMoreTimes()->withAnyArgs()->andReturn(false);
        $connection->shouldReceive('executeStatement')->once()->withAnyArgs()->andThrow(DBALException::invalidTableName('abc'));
        $connection->shouldReceive('executeStatement')->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();

        /** @var Connection $connection */

        $this->createDatabaseSchema($connection, new DatabaseSchema(User::TABLE_NAME, User::FIELD_ID));
    }
}
