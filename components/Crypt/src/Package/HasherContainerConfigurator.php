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

namespace Whoa\Crypt\Package;

use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Crypt\Contracts\HasherInterface;
use Whoa\Crypt\Hasher;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Whoa\Crypt\Package\HasherSettings as C;

/**
 * @package Whoa\Crypt
 */
class HasherContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[HasherInterface::class] = function (PsrContainerInterface $container): HasherInterface {
            $settings = $container->get(SettingsProviderInterface::class)->get(C::class);
            $hasher   = new Hasher($settings[C::KEY_ALGORITHM], $settings[C::KEY_COST]);

            return $hasher;
        };
    }
}
