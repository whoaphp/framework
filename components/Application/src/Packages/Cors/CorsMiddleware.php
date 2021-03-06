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

use Closure;
use Whoa\Contracts\Application\MiddlewareInterface;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use function assert;

/**
 * @package Whoa\Application
 */
class CorsMiddleware implements MiddlewareInterface
{
    /** Middleware handler */
    const CALLABLE_HANDLER = [self::class, self::MIDDLEWARE_METHOD_NAME];

    /**
     * @inheritdoc
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function handle(
        ServerRequestInterface $request,
        Closure                $next,
        ContainerInterface     $container
    ): ResponseInterface
    {
        /** @var AnalyzerInterface $analyzer */
        $analyzer = $container->get(AnalyzerInterface::class);
        $analysis = $analyzer->analyze($request);

        $requestType = $analysis->getRequestType();
        switch ($requestType) {
            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                // call next middleware handler
                return $next($request);

            case AnalysisResultInterface::TYPE_ACTUAL_REQUEST:
                // actual CORS request
                $corsHeaders = $analysis->getResponseHeaders();

                /** @var ResponseInterface $response */
                $response = $next($request);

                // add CORS headers to Response $response
                foreach ($corsHeaders as $name => $value) {
                    $response = $response->withHeader($name, $value);
                }

                return $response;

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                // return 200 HTTP with $corsHeaders
                return new EmptyResponse(200, $analysis->getResponseHeaders());

            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
                return static::getErrorOriginNotAllowedResponse($analysis);

            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
                return static::getErrorMethodNotSupportedResponse($analysis);

            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                return static::getErrorHeadersNotSupportedResponse($analysis);

            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            default:
                assert($requestType === AnalysisResultInterface::ERR_NO_HOST_HEADER);

                return static::getErrorNoHostHeaderResponse($analysis);
        }
    }

    /**
     * @param AnalysisResultInterface $analysis
     *
     * @return ResponseInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function getErrorNoHostHeaderResponse(
        /** @noinspection PhpUnusedParameterInspection */ AnalysisResultInterface $analysis
    ): ResponseInterface
    {
        return new EmptyResponse(400);
    }

    /**
     * @param AnalysisResultInterface $analysis
     *
     * @return ResponseInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function getErrorOriginNotAllowedResponse(
        /** @noinspection PhpUnusedParameterInspection */ AnalysisResultInterface $analysis
    ): ResponseInterface
    {
        return new EmptyResponse(400);
    }

    /**
     * @param AnalysisResultInterface $analysis
     *
     * @return ResponseInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function getErrorMethodNotSupportedResponse(
        /** @noinspection PhpUnusedParameterInspection */ AnalysisResultInterface $analysis
    ): ResponseInterface
    {
        return new EmptyResponse(400);
    }

    /**
     * @param AnalysisResultInterface $analysis
     *
     * @return ResponseInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function getErrorHeadersNotSupportedResponse(
        /** @noinspection PhpUnusedParameterInspection */ AnalysisResultInterface $analysis
    ): ResponseInterface
    {
        return new EmptyResponse(400);
    }
}
