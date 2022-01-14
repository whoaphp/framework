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
use Whoa\Crypt\Exceptions\CryptConfigurationException;
use Whoa\Crypt\Package\AsymmetricCryptSettings as C;
use Whoa\Crypt\PrivateKeyAsymmetricDecrypt;
use Whoa\Crypt\PublicKeyAsymmetricEncrypt;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @package Whoa\Crypt
 */
class AsymmetricPublicEncryptPrivateDecryptContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[EncryptInterface::class] = function (PsrContainerInterface $container): EncryptInterface {
            $settings  = $container->get(SettingsProviderInterface::class)->get(C::class);
            $keyOrPath = $settings[C::KEY_PUBLIC_PATH_OR_KEY_VALUE] ?? null;
            if (empty($keyOrPath) === true) {
                throw new CryptConfigurationException();
            }

            $crypt = new PublicKeyAsymmetricEncrypt($keyOrPath);

            return $crypt;
        };

        $container[DecryptInterface::class] = function (PsrContainerInterface $container): DecryptInterface {
            $settings  = $container->get(SettingsProviderInterface::class)->get(C::class);
            $keyOrPath = $settings[C::KEY_PRIVATE_PATH_OR_KEY_VALUE] ?? null;
            if (empty($keyOrPath) === true) {
                throw new CryptConfigurationException();
            }

            $crypt = new PrivateKeyAsymmetricDecrypt($keyOrPath);

            return $crypt;
        };
    }
}
