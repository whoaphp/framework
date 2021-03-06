<?php

/*
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

namespace Whoa\Tests\Application\Commands;

use Whoa\Application\Commands\DataCommand;
use Whoa\Application\Data\FileMigrationRunner;
use Whoa\Application\Data\FileSeedRunner;
use Whoa\Application\Packages\Data\DataSettings;
use Whoa\Container\Container;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Whoa\Tests\Application
 */
class DataCommandTest extends TestCase
{
    /**
     * Test and add coverage for methods we would mock in main tests.
     *
     * @throws ReflectionException
     */
    public function testCoverCreateRunnerMethods(): void
    {
        $command = new DataCommand();
        $inOut = Mockery::mock(IoInterface::class);

        $method = new ReflectionMethod(DataCommand::class, 'createMigrationRunner');
        $method->setAccessible(true);
        $this->assertInstanceOf(FileMigrationRunner::class, $method->invoke($command, $inOut, '/some/path'));

        $method = new ReflectionMethod(DataCommand::class, 'createSeedRunner');
        $method->setAccessible(true);
        $this->assertInstanceOf(FileSeedRunner::class, $method->invoke($command, $inOut, '/some/path'));
    }

    /**
     * Test command descriptions.
     */
    public function testCommandDescriptions(): void
    {
        $this->assertNotEmpty(DataCommand::getName());
        $this->assertNotEmpty(DataCommand::getHelp());
        $this->assertNotEmpty(DataCommand::getDescription());
        $this->assertNotEmpty(DataCommand::getArguments());
        $this->assertNotEmpty(DataCommand::getOptions());
    }

    /**
     * Test command called with invalid action parameter.
     */
    public function testInvalidAction(): void
    {
        $container = $this->createContainerWithDataSettings([
            DataSettings::KEY_MIGRATIONS_FOLDER => '/some/path',
        ]);
        $inOut = $this->createInOutMock(
            [
                DataCommand::ARG_ACTION => 'non_existing_action',
            ],
            [
                DataCommand::OPT_PATH => '/some/path',
            ],
            true
        );

        DataCommand::execute($container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     *
     * @throws ReflectionException
     */
    public function testMigrate(): void
    {
        $container = $this->createContainerWithDataSettings([
            DataSettings::KEY_MIGRATIONS_FOLDER => '/some/path',
        ]);
        $inOut = $this->createInOutMock([
            DataCommand::ARG_ACTION => DataCommand::ACTION_MIGRATE,
        ], [
            DataCommand::OPT_PATH => '/some/path',
        ]);

        $method = new ReflectionMethod(DataCommand::class, 'run');
        $method->setAccessible(true);
        $command = $this->createCommandMock(
            $this->createMigrationRunnerMock('migrate'),
            $this->createSeedRunnerMock()
        );
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     *
     * @throws ReflectionException
     */
    public function testRollback(): void
    {
        $container = $this->createContainerWithDataSettings([
            DataSettings::KEY_MIGRATIONS_FOLDER => '/some/path',
        ]);
        $inOut = $this->createInOutMock([
            DataCommand::ARG_ACTION => DataCommand::ACTION_ROLLBACK,
        ], [
            DataCommand::OPT_PATH => '/some/path',
        ]);

        $method = new ReflectionMethod(DataCommand::class, 'run');
        $method->setAccessible(true);
        $command = $this->createCommandMock(
            $this->createMigrationRunnerMock('rollback'),
            $this->createSeedRunnerMock()
        );
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     *
     * @throws ReflectionException
     */
    public function testSeed(): void
    {
        $container = $this->createContainerWithDataSettings([
            DataSettings::KEY_MIGRATIONS_FOLDER => '/some/path',
        ]);
        $inOut = $this->createInOutMock([
            DataCommand::ARG_ACTION => DataCommand::ACTION_SEED,
        ], [
            DataCommand::OPT_PATH => '/some/path',
        ]);

        $method = new ReflectionMethod(DataCommand::class, 'run');
        $method->setAccessible(true);
        $command = $this->createCommandMock(
            $this->createMigrationRunnerMock(),
            $this->createSeedRunnerMock('run')
        );
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * @param array $settings
     *
     * @return Container
     */
    private function createContainerWithDataSettings(array $settings): Container
    {
        $container = new Container();

        /** @var Mock $providerMock */
        $container[SettingsProviderInterface::class] = $providerMock = Mockery::mock(SettingsProviderInterface::class);
        $providerMock->shouldReceive('get')->zeroOrMoreTimes()->with(DataSettings::class)->andReturn($settings);

        return $container;
    }

    /**
     * @param array $arguments
     * @param array $options
     * @param bool $expectErrors
     *
     * @return IoInterface
     */
    private function createInOutMock(array $arguments, array $options, bool $expectErrors = false): IoInterface
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(IoInterface::class);
        $mock->shouldReceive('getArguments')->zeroOrMoreTimes()->withNoArgs()->andReturn($arguments);
        $mock->shouldReceive('getOptions')->zeroOrMoreTimes()->withNoArgs()->andReturn($options);
        if ($expectErrors === true) {
            $mock->shouldReceive('writeError')->zeroOrMoreTimes()->withAnyArgs()->andReturnSelf();
        }

        /** @var IoInterface $mock */

        return $mock;
    }

    /**
     * @param FileMigrationRunner $migrationMock
     * @param FileSeedRunner $seedMock
     *
     * @return DataCommand
     */
    private function createCommandMock(FileMigrationRunner $migrationMock, FileSeedRunner $seedMock): DataCommand
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(DataCommand::class . '[createMigrationRunner,createSeedRunner]');
        $mock->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('createMigrationRunner')->zeroOrMoreTimes()->withAnyArgs()->andReturn($migrationMock);
        $mock->shouldReceive('createSeedRunner')->zeroOrMoreTimes()->withAnyArgs()->andReturn($seedMock);

        /** @var DataCommand $mock */

        return $mock;
    }

    /**
     * @param string|null $expectedMethod
     *
     * @return FileMigrationRunner
     */
    private function createMigrationRunnerMock(string $expectedMethod = null): FileMigrationRunner
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(FileMigrationRunner::class);

        if ($expectedMethod !== null) {
            $mock->shouldReceive($expectedMethod)->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();
        }

        /** @var FileMigrationRunner $mock */

        return $mock;
    }

    /**
     * @param string|null $expectedMethod
     *
     * @return FileSeedRunner
     */
    private function createSeedRunnerMock(string $expectedMethod = null): FileSeedRunner
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(FileSeedRunner::class);

        if ($expectedMethod !== null) {
            $mock->shouldReceive($expectedMethod)->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();
        }

        /** @var FileSeedRunner $mock */

        return $mock;
    }
}
