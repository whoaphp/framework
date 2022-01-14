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
use Whoa\Crypt\Contracts\DecryptInterface;
use Whoa\Crypt\Contracts\EncryptInterface;
use Whoa\Crypt\SymmetricCrypt;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Whoa\Crypt\Package\SymmetricCryptSettings as C;
use function array_key_exists;

/**
 * @package Whoa\Crypt
 */
class SymmetricCryptContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $crypt = null;

        $instanceFactory = function (PsrContainerInterface $container) use (&$crypt) {
            if ($crypt === null) {
                $settings = $container->get(SettingsProviderInterface::class)->get(C::class);
                $crypt    = new SymmetricCrypt($settings[C::KEY_METHOD], $settings[C::KEY_PASSWORD]);

                $vector = $settings[C::KEY_IV] ?? '';
                empty($vector) === true ?: $crypt->setIV($vector);

                $usePadding = $settings[C::KEY_USE_ZERO_PADDING] ?? false;
                $usePadding === true ? $crypt->withZeroPadding() : $crypt->withoutZeroPadding();

                $useAuthentication = $settings[C::KEY_USE_AUTHENTICATION] ?? false;
                if ($useAuthentication === true) {
                    $crypt->enableAuthentication();
                    if (array_key_exists(C::KEY_TAG_LENGTH, $settings) === true) {
                        $tagLength = $settings[C::KEY_TAG_LENGTH];
                        $crypt->setTagLength($tagLength);
                    }
                }
            }

            return $crypt;
        };

        $container[EncryptInterface::class] = $instanceFactory;
        $container[DecryptInterface::class] = $instanceFactory;
    }
}
