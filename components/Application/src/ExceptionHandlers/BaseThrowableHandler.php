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

namespace Whoa\Application\ExceptionHandlers;

use Exception;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Exceptions\ThrowableHandlerInterface;
use Whoa\Contracts\Http\ThrowableResponseInterface;
use Whoa\Core\Application\ThrowableResponseTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

/**
 * @package Whoa\Application
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class BaseThrowableHandler implements ThrowableHandlerInterface
{
    /** Default HTTP code. */
    protected const DEFAULT_HTTP_ERROR_CODE = 500;

    /**
     * @param ContainerInterface $container
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    protected function getSettings(ContainerInterface $container): array
    {
        $appConfig = null;

        /** @var CacheSettingsProviderInterface $settingsProvider */
        if ($container->has(CacheSettingsProviderInterface::class) === true &&
            ($settingsProvider = $container->get(CacheSettingsProviderInterface::class)) !== null
        ) {
            $appConfig = $settingsProvider->getApplicationConfiguration();
        }

        return [
            $appConfig[A::KEY_IS_DEBUG] ?? false,
            $appConfig[A::KEY_APP_NAME] ?? null,
            $appConfig[A::KEY_EXCEPTION_DUMPER] ?? null,
        ];
    }

    /**
     * @param Throwable $exception
     * @param ContainerInterface $container
     * @param string $message
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    protected function logException(Throwable $exception, ContainerInterface $container, string $message): void
    {
        if ($container->has(LoggerInterface::class) === true) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);

            // The sad truth is that when you have a problem logging might not be available (e.g. no permissions
            // to write on a disk). We can't do much with it and can only hope that the error information will be
            // delivered to the user other way.
            try {
                $logger->critical($message, ['exception' => $exception]);
            } catch (Exception $secondException) {
            }
        }
    }

    /**
     * @param Throwable $throwable
     * @param string $text
     * @param int $status
     *
     * @return ThrowableResponseInterface
     */
    protected function createThrowableTextResponse(
        Throwable $throwable,
        string    $text,
        int       $status
    ): ThrowableResponseInterface
    {
        return new class ($throwable, $text, $status) extends TextResponse implements ThrowableResponseInterface {
            use ThrowableResponseTrait;

            /**
             * @param Throwable $throwable
             * @param string $text
             * @param int $status
             */
            public function __construct(Throwable $throwable, string $text, int $status)
            {
                parent::__construct($text, $status);
                $this->setThrowable($throwable);
            }
        };
    }

    /**
     * @param Throwable $throwable
     * @param string $text
     * @param int $status
     *
     * @return ThrowableResponseInterface
     */
    protected function createThrowableHtmlResponse(
        Throwable $throwable,
        string    $text,
        int       $status
    ): ThrowableResponseInterface
    {
        return new class ($throwable, $text, $status) extends HtmlResponse implements ThrowableResponseInterface {
            use ThrowableResponseTrait;

            /**
             * @param Throwable $throwable
             * @param string $text
             * @param int $status
             */
            public function __construct(Throwable $throwable, string $text, int $status)
            {
                parent::__construct($text, $status);
                $this->setThrowable($throwable);
            }
        };
    }

    /**
     * @param Throwable $throwable
     * @param string $json
     * @param int $status
     *
     * @return ThrowableResponseInterface
     */
    protected function createThrowableJsonResponse(
        Throwable $throwable,
        string    $json,
        int       $status
    ): ThrowableResponseInterface
    {
        return new class ($throwable, $json, $status) extends JsonResponse implements ThrowableResponseInterface {
            use ThrowableResponseTrait;

            /**
             * @param Throwable $throwable
             * @param string $json
             * @param int $status
             */
            public function __construct(Throwable $throwable, string $json, int $status)
            {
                parent::__construct($json, $status);
                $this->setThrowable($throwable);
            }
        };
    }
}
