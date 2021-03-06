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

namespace Whoa\Tests\Commands;

use Composer\Composer;
use Exception;
use Whoa\Commands\CommandConstants;
use Whoa\Commands\WhoaCommand;
use Whoa\Commands\Traits\CacheFilePathTrait;
use Whoa\Commands\Traits\CommandSerializationTrait;
use Whoa\Commands\Traits\CommandTrait;
use Whoa\Contracts\Application\ApplicationConfigurationInterface;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Commands\CommandInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Exceptions\ThrowableHandlerInterface;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Contracts\Http\ThrowableResponseInterface;
use Whoa\Tests\Commands\Data\TestApplication;
use Whoa\Tests\Commands\Data\TestCliRoutesConfigurator;
use Whoa\Tests\Commands\Data\TestCommand;
use Mockery;
use Mockery\MockInterface;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Whoa\Tests\Commands
 */
class WhoaCommandTest extends TestCase
{
    use CacheFilePathTrait, CommandSerializationTrait, CommandTrait;

    /** @var bool */
    private static $executedFlag = false;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::$executedFlag = false;
        TestCliRoutesConfigurator::clearTestFlags();
    }

    /**
     * Test basic command behaviour.
     *
     * @throws Exception
     */
    public function testCommand(): void
    {
        $name    = TestCliRoutesConfigurator::COMMAND_NAME_1;
        $command = $this->createCommandMock($name);

        $command->shouldReceive('createContainer')->once()->withAnyArgs()->andReturn($this->createContainerMock());

        /** @var WhoaCommand $command */

        $this->assertEquals($name, $command->getName());

        $input    = Mockery::mock(InputInterface::class);
        $output   = Mockery::mock(OutputInterface::class);
        $composer = Mockery::mock(Composer::class);

        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        /** @var Composer $composer */

        $command->setComposer($composer);

        $this->assertFalse(TestCliRoutesConfigurator::areHandlersExecuted1());
        $this->assertFalse(TestCliRoutesConfigurator::areHandlersExecuted2());

        $command->execute($input, $output);

        $this->assertTrue(static::$executedFlag);
        $this->assertTrue(TestCliRoutesConfigurator::areHandlersExecuted1());
        $this->assertFalse(TestCliRoutesConfigurator::areHandlersExecuted2());
    }

    /**
     * Test if container creation fails.
     *
     * @throws Exception
     */
    public function testContainerCreationFails(): void
    {
        $command = $this->createCommandMock();

        $command->shouldReceive('createContainer')
            ->once()->withAnyArgs()->andThrow(new Exception('Oops, container failed'));

        /** @var WhoaCommand $command */

        $input    = Mockery::mock(InputInterface::class);
        $output   = Mockery::mock(OutputInterface::class);
        $composer = Mockery::mock(Composer::class);

        $output->shouldReceive('writeln')->once()->withAnyArgs()->andReturnUndefined();

        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        /** @var Composer $composer */

        $command->setComposer($composer);

        $exception = null;
        try {
            $command->execute($input, $output);
        } catch (Exception $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertStringStartsWith('Oops, container failed', $exception->getMessage());

        $this->assertFalse(static::$executedFlag);
    }

    /**
     * Test custom error handler.
     *
     * @throws Exception
     */
    public function testCustomErrorHandler(): void
    {
        $command = $this->createCommandMock('name', [self::class, 'callbackWithThrow']);

        $handler   = Mockery::mock(ThrowableHandlerInterface::class);
        $response  = Mockery::mock(ThrowableResponseInterface::class);
        $container = $this->createContainerMock();
        $container->shouldReceive('has')->withArgs([ThrowableHandlerInterface::class])->andReturn(true);
        $container->shouldReceive('get')->withArgs([ThrowableHandlerInterface::class])->andReturn($handler);
        $handler->shouldReceive('createResponse')->once()->withAnyArgs()->andReturn($response);
        $response->shouldReceive('getBody')->once()->withAnyArgs()->andReturn('does not matter');


        $command->shouldReceive('createContainer')->once()->withAnyArgs()->andReturn($container);

        /** @var WhoaCommand $command */

        $input    = Mockery::mock(InputInterface::class);
        $output   = Mockery::mock(OutputInterface::class);
        $composer = Mockery::mock(Composer::class);

        $output->shouldReceive('writeln')->once()->withAnyArgs()->andReturnUndefined();

        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        /** @var Composer $composer */

        $command->setComposer($composer);

        $exception = null;
        try {
            $command->execute($input, $output);
        } catch (Exception $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertStringStartsWith('Oops, command thrown and exception', $exception->getMessage());

        $this->assertTrue(static::$executedFlag);
    }

    /**
     * Test trait method.
     *
     * @throws Exception
     */
    public function testGetCommandsCacheFilePath(): void
    {
        /** @var Mockery\Mock $composer */
        $composer = Mockery::mock(Composer::class);

        $fileName = 'composer.json';
        $composer->shouldReceive('getPackage')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('getExtra')->once()->withNoArgs()->andReturn([
            CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION => [
                CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION__COMMANDS_CACHE => $fileName,
            ],
        ]);

        $vendorDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor';
        $composer->shouldReceive('getConfig')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('get')->once()->with('vendor-dir')->andReturn($vendorDir);

        /** @var Composer $composer */

        $path     = $this->getCommandsCacheFilePath($composer);
        $expected = realpath($vendorDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $fileName);
        $this->assertEquals($expected, $path);
    }

    /**
     * Test trait method.
     *
     * @throws Exception
     */
    public function testCommandSerialization(): void
    {
        $this->assertNotEmpty($this->commandClassToArray(TestCommand::class));
    }

    /**
     * Test trait method.
     *
     * @throws Exception
     */
    public function testCreateContainer(): void
    {
        /** @var Mockery\Mock $composer */
        $composer = Mockery::mock(Composer::class);

        $vendorDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor';
        $composer->shouldReceive('getConfig')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('get')->once()->with('vendor-dir')->andReturn($vendorDir);

        $extra = [
            CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION => [
                CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION__CLASS => TestApplication::class,
            ],
        ];
        $composer->shouldReceive('getPackage')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('getExtra')->once()->withNoArgs()->andReturn($extra);

        /** @var Composer $composer */

        $this->assertNotNull($this->createContainer($composer));
    }

    /**
     * Test trait method.
     *
     * @throws ReflectionException
     */
    public function testCreateContainerForInvalidAppClass(): void
    {

        $this->expectException(\Whoa\Commands\Exceptions\ConfigurationException::class);

        /** @var Mockery\Mock $composer */
        $composer = Mockery::mock(Composer::class);

        $vendorDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor';
        $composer->shouldReceive('getConfig')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('get')->once()->with('vendor-dir')->andReturn($vendorDir);

        $extra = [
            CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION => [
                CommandConstants::COMPOSER_JSON__EXTRA__APPLICATION__CLASS => self::class, // <-- invalid App class
            ],
        ];
        $composer->shouldReceive('getPackage')->once()->withNoArgs()->andReturnSelf();
        $composer->shouldReceive('getExtra')->once()->withNoArgs()->andReturn($extra);

        /** @var Composer $composer */

        $this->createContainer($composer);
    }

    /**
     * @return void
     */
    public static function callback1(): void
    {
        static::$executedFlag = true;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public static function callbackWithThrow(): void
    {
        static::$executedFlag = true;

        throw new Exception('Oops, command thrown and exception');
    }

    /**
     * @param string $name
     *
     * @param array  $callable
     *
     * @return Mockery\Mock
     */
    private function createCommandMock(string $name = 'name', $callable = [self::class, 'callback1'])
    {
        $description = 'description';
        $help        = 'help';
        $argName1    = 'arg1';
        $arguments   = [
            [
                CommandInterface::ARGUMENT_NAME => $argName1,
            ],
        ];
        $optName1    = 'opt1';
        $options     = [
            [
                CommandInterface::OPTION_NAME => $optName1,
            ],
        ];

        /** @var Mockery\Mock $command */
        $command = Mockery::mock(
            WhoaCommand::class . '[createContainer]',
            [$name, $description, $help, $arguments, $options, $callable]
        );
        $command->shouldAllowMockingProtectedMethods();

        return $command;
    }

    /**
     * @return MockInterface
     */
    private function createContainerMock(): MockInterface
    {
        $container = Mockery::mock(WhoaContainerInterface::class);

        // add some app settings to container
        $routesFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, 'Data',]);
        $container
            ->shouldReceive('get')->once()
            ->with(CacheSettingsProviderInterface::class)
            ->andReturnSelf();
        $container
            ->shouldReceive('getApplicationConfiguration')->once()
            ->withNoArgs()
            ->andReturn([
                ApplicationConfigurationInterface::KEY_ROUTES_FOLDER    => $routesFolder,
                ApplicationConfigurationInterface::KEY_ROUTES_FILE_MASK => '*.php',
            ]);
        // add FileSystem to container
        $container
            ->shouldReceive('get')->once()
            ->with(FileSystemInterface::class)
            ->andReturnSelf();
        $container
            ->shouldReceive('exists')->once()
            ->with($routesFolder)
            ->andReturn(true);

        return $container;
    }
}
