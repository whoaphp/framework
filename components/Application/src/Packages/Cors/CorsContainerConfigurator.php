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

namespace Whoa\Application\Packages\Cors;

use Whoa\Application\Packages\Cors\CorsSettings as C;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Neomerx\Cors\Strategies\Settings;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Whoa\Application
 */
class CorsContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[AnalyzerInterface::class] = function (PsrContainerInterface $container) {
            $settingsProvider = $container->get(SettingsProviderInterface::class);

            [$corsSettings, $isLogEnabled] = $settingsProvider->get(C::class);

            $analyzer = Analyzer::instance((new Settings())->setData($corsSettings));

            if ($isLogEnabled === true && $container->has(LoggerInterface::class)) {
                $logger = $container->get(LoggerInterface::class);
                $analyzer->setLogger($logger);
            }

            return $analyzer;
        };
    }
}
