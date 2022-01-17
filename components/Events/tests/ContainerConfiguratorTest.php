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

namespace Whoa\Tests\Events;

use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Events\Contracts\EventDispatcherInterface;
use Whoa\Events\Contracts\EventEmitterInterface;
use Whoa\Events\Package\EventProvider;
use Whoa\Events\Package\EventSettings as BaseEventSettings;
use Whoa\Tests\Events\Data\EventSettings;
use Whoa\Tests\Events\Data\TestContainer;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @package Whoa\Tests\Events
 */
class ContainerConfiguratorTest extends TestCase
{
    /**
     * Test provider.
     *
     * @throws ReflectionException
     */
    public function testEventProvider()
    {
        /** @var ContainerConfiguratorInterface $configuratorClass */
        [$configuratorClass] = EventProvider::getContainerConfigurators();
        $container = new TestContainer();

        $appConfig = [];
        $this->addSettings($container, BaseEventSettings::class, (new EventSettings())->get($appConfig));

        $configuratorClass::configureContainer($container);

        $this->assertNotNull($container->get(EventEmitterInterface::class));
        $this->assertNotNull($container->get(EventDispatcherInterface::class));
    }

    /**
     * @param ContainerInterface $container
     * @param string             $settingsClass
     * @param array              $settings
     *
     * @return self
     */
    private function addSettings(ContainerInterface $container, string $settingsClass, array $settings): self
    {
        /** @var Mock $settingsMock */
        $settingsMock = Mockery::mock(SettingsProviderInterface::class);
        $settingsMock->shouldReceive('get')->once()->with($settingsClass)->andReturn($settings);

        $container->offsetSet(SettingsProviderInterface::class, $settingsMock);

        return $this;
    }
}
