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

namespace Whoa\Events\Package;

use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Events\Contracts\EventDispatcherInterface;
use Whoa\Events\Contracts\EventEmitterInterface;
use Whoa\Events\Package\EventSettings as C;
use Whoa\Events\SimpleEventEmitter;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use function call_user_func;

/**
 * @package Whoa\Events
 */
class EventsContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $emitter            = null;
        $getOrCreateEmitter = function (PsrContainerInterface $container) use (&$emitter): SimpleEventEmitter {
            if ($emitter === null) {
                $emitter   = new SimpleEventEmitter();
                $cacheData = $container->get(SettingsProviderInterface::class)->get(C::class)[C::KEY_CACHED_DATA];
                $emitter->setData($cacheData);
            }

            return $emitter;
        };

        $container[EventEmitterInterface::class] =
            function (PsrContainerInterface $container) use ($getOrCreateEmitter): EventEmitterInterface {
                return call_user_func($getOrCreateEmitter, $container);
            };

        $container[EventDispatcherInterface::class] =
            function (PsrContainerInterface $container) use ($getOrCreateEmitter): EventDispatcherInterface {
                return call_user_func($getOrCreateEmitter, $container);
            };
    }
}
