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

namespace Whoa\Tests\Passport\Package;

use Doctrine\DBAL\Connection;
use Exception;
use Whoa\Core\Routing\Group;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Package\PassportMigration;
use Whoa\Passport\Package\PassportProvider;
use Whoa\Passport\Package\PassportRoutesConfigurator;
use Whoa\Tests\Passport\Data\TestContainer;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @package Whoa\Tests\Templates
 */
class PassportRoutesConfiguratorTest extends TestCase
{

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test settings could be instantiated.
     *
     * @throws Exception
     */
    public function testGetSettings()
    {
        $routes = new Group();
        PassportRoutesConfigurator::configureRoutes($routes);
        /** @noinspection PhpParamsInspection */
        $this->assertCount(2, iterator_to_array($routes->getRoutes()));

        $this->assertEmpty(PassportRoutesConfigurator::getMiddleware());
    }

    /**
     * Test provider.
     *
     * @throws Exception
     */
    public function testProvider()
    {
        $this->assertNotEmpty(PassportProvider::getMiddleware());
        $this->assertNotEmpty(PassportProvider::getMigrations());
        $this->assertNotEmpty(PassportProvider::getRouteConfigurators());
        $this->assertNotEmpty(PassportProvider::getContainerConfigurators());
    }

    /**
     * Test migration.
     *
     * @throws Exception
     */
    public function testMigrationMigrate()
    {
        $migration = Mockery::mock(PassportMigration::class . '[createDatabaseSchema]')
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $migration->shouldReceive('createDatabaseSchema')->once()->withAnyArgs()->andReturnUndefined();

        $container                                 = new TestContainer();
        $container[Connection::class]              = Mockery::mock(Connection::class);
        $container[DatabaseSchemaInterface::class] = Mockery::mock(DatabaseSchemaInterface::class);

        /** @var PassportMigration $migration */

        $migration->init($container)->migrate();

        // assert it executed exactly as described above and we need at lease 1 assert to avoid PHP unit warning.
        $this->assertTrue(true);
    }

    /**
     * Test migration rollback.
     *
     * @throws Exception
     */
    public function testMigrationRollback()
    {
        $migration = Mockery::mock(PassportMigration::class . '[removeDatabaseSchema]')
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $migration->shouldReceive('removeDatabaseSchema')->once()->withAnyArgs()->andReturnUndefined();

        $container                                 = new TestContainer();
        $container[Connection::class]              = Mockery::mock(Connection::class);
        $container[DatabaseSchemaInterface::class] = Mockery::mock(DatabaseSchemaInterface::class);

        /** @var PassportMigration $migration */

        $migration->init($container)->rollback();

        // assert it executed exactly as described above and we need at lease 1 assert to avoid PHP unit warning.
        $this->assertTrue(true);
    }
}
