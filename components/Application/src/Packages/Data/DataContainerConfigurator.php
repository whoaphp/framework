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

namespace Whoa\Application\Packages\Data;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Whoa\Application\Data\ModelSchemaInfo;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use function array_filter;
use function array_key_exists;
use function is_array;

/**
 * @package Whoa\Application
 */
class DataContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[ModelSchemaInfoInterface::class] =
            function (PsrContainerInterface $container): ModelSchemaInfoInterface {
                $settings = $container->get(SettingsProviderInterface::class)->get(DataSettings::class);
                $data = $settings[DataSettings::KEY_MODELS_SCHEMA_INFO];

                return (new ModelSchemaInfo())->setData($data);
            };

        $container[Connection::class] = function (PsrContainerInterface $container): Connection {
            $settings = $container->get(SettingsProviderInterface::class)->get(DoctrineSettings::class);
            $params = array_filter([
                'driver' => $settings[DoctrineSettings::KEY_DRIVER] ?? null,
                'dbname' => $settings[DoctrineSettings::KEY_DATABASE_NAME] ?? null,
                'user' => $settings[DoctrineSettings::KEY_USER_NAME] ?? null,
                'password' => $settings[DoctrineSettings::KEY_PASSWORD] ?? null,
                'host' => $settings[DoctrineSettings::KEY_HOST] ?? null,
                'port' => $settings[DoctrineSettings::KEY_PORT] ?? null,
                'url' => $settings[DoctrineSettings::KEY_URL] ?? null,
                'memory' => $settings[DoctrineSettings::KEY_MEMORY] ?? null,
                'path' => $settings[DoctrineSettings::KEY_PATH] ?? null,
                'charset' => $settings[DoctrineSettings::KEY_CHARSET] ?? 'UTF8',
            ], function ($value) {
                return $value !== null;
            });
            $extra = $settings[DoctrineSettings::KEY_EXTRA] ?? [];

            $connection = DriverManager::getConnection($params + $extra);

            if (array_key_exists(DoctrineSettings::KEY_EXEC, $settings) === true &&
                is_array($toExec = $settings[DoctrineSettings::KEY_EXEC]) === true &&
                empty($toExec) === false
            ) {
                foreach ($toExec as $statement) {
                    $connection->exec($statement);
                }
            }

            return $connection;
        };
    }
}
