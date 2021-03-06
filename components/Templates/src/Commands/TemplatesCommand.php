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

namespace Whoa\Templates\Commands;

use Whoa\Contracts\Commands\CommandInterface;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Templates\Contracts\TemplatesCacheInterface;
use Whoa\Templates\Package\TemplatesSettings;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @package Whoa\Templates
 */
class TemplatesCommand implements CommandInterface
{
    /**
     * Command name.
     */
    const NAME = 'w:templates';

    /** Argument name */
    const ARG_ACTION = 'action';

    /** Command action */
    const ACTION_CLEAR_CACHE = 'clear-cache';

    /** Command action */
    const ACTION_CREATE_CACHE = 'cache';

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public static function getDescription(): string
    {
        return 'Creates and cleans templates cache.';
    }

    /**
     * @inheritdoc
     */
    public static function getHelp(): string
    {
        return 'This command creates and cleans cache for HTML templates.';
    }

    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        $cache = static::ACTION_CREATE_CACHE;
        $clear = static::ACTION_CLEAR_CACHE;

        return [
            [
                static::ARGUMENT_NAME        => static::ARG_ACTION,
                static::ARGUMENT_DESCRIPTION => "Action such as `$cache` or `$clear`.",
                static::ARGUMENT_MODE        => static::ARGUMENT_MODE__REQUIRED,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getOptions(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function execute(ContainerInterface $container, IoInterface $inOut): void
    {
        $action = $inOut->getArgument(static::ARG_ACTION);
        switch ($action) {
            case static::ACTION_CREATE_CACHE:
                (new self())->executeCache($inOut, $container);
                break;
            case static::ACTION_CLEAR_CACHE:
                (new self())->executeClear($inOut, $container);
                break;
            default:
                $inOut->writeError("Unsupported action `$action`." . PHP_EOL);
                break;
        }
    }

    /**
     * @param IoInterface        $inOut
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function executeClear(IoInterface $inOut, ContainerInterface $container): void
    {
        $settings    = $this->getTemplatesSettings($container);
        $cacheFolder = $settings[TemplatesSettings::KEY_CACHE_FOLDER];

        /** @var FileSystemInterface $fileSystem */
        $fileSystem = $container->get(FileSystemInterface::class);
        foreach ($fileSystem->scanFolder($cacheFolder) as $fileOrFolder) {
            $fileSystem->isFolder($fileOrFolder) === false ?: $fileSystem->deleteFolderRecursive($fileOrFolder);
        }

        $inOut->writeInfo('Cache has been cleared.' . PHP_EOL);
    }

    /**
     * @param IoInterface        $inOut
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function executeCache(IoInterface $inOut, ContainerInterface $container): void
    {
        /** @var TemplatesCacheInterface $cache */
        $cache = $container->get(TemplatesCacheInterface::class);

        $settings  = $this->getTemplatesSettings($container);
        $templates = $settings[TemplatesSettings::KEY_TEMPLATES_LIST];

        foreach ($templates as $templateName) {
            // it will write template to cache
            $inOut->writeInfo(
                "Starting template caching for `$templateName`..." . PHP_EOL,
                IoInterface::VERBOSITY_VERBOSE
            );

            $cache->cache($templateName);

            $inOut->writeInfo(
                "Template caching finished for `$templateName`." . PHP_EOL,
                IoInterface::VERBOSITY_NORMAL
            );
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getTemplatesSettings(ContainerInterface $container): array
    {
        $tplConfig = $container->get(SettingsProviderInterface::class)->get(TemplatesSettings::class);

        return $tplConfig;
    }
}
