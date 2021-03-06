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

namespace Whoa\Tests\Core\Routing;

use Exception;
use FastRoute\DataGenerator\CharCountBased as CharCountBasedGenerator;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\DataGenerator\GroupPosBased as GroupPosBasedGenerator;
use FastRoute\DataGenerator\MarkBased as MarkBasedGenerator;
use Whoa\Contracts\Core\SapiInterface;
use Whoa\Contracts\Routing\GroupInterface;
use Whoa\Contracts\Routing\RouteInterface;
use Whoa\Contracts\Routing\RouterInterface;
use Whoa\Core\Routing\Dispatcher\CharCountBased;
use Whoa\Core\Routing\Dispatcher\GroupCountBased;
use Whoa\Core\Routing\Dispatcher\GroupPosBased;
use Whoa\Core\Routing\Dispatcher\MarkBased;
use Whoa\Core\Routing\Group;
use Whoa\Core\Routing\Router;
use Whoa\Tests\Core\TestCase;
use LogicException;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

/**
 * @package Whoa\Tests\Core
 */
class RouterTest extends TestCase
{
    /** Test route name */
    const ROUTE_NAME_DELETE_POST = 'deletePost';

    /**
     * Test match routes.
     *
     * @throws Exception
     */
    public function testMatchForGroupCountBasedRouter(): void
    {
        $this->checkMatchRoutes($this->createGroupCountBasedRouter());
    }

    /**
     * Test match routes.
     *
     * @throws Exception
     */
    public function testMatchForGroupPosBasedRouter(): void
    {
        $this->checkMatchRoutes($this->createGroupPosBasedRouter());
    }

    /**
     * Test match routes.
     *
     * @throws Exception
     */
    public function testMatchForCharCountBasedRouter(): void
    {
        $this->checkMatchRoutes($this->createCharCountBasedRouter());
    }

    /**
     * Test match routes.
     *
     * @throws Exception
     */
    public function testMatchForMarkBasedRouter(): void
    {
        $this->checkMatchRoutes($this->createMarkBasedRouter());
    }

    /**
     * Test loading of cached data.
     *
     * @throws Exception
     */
    public function testLoadCachedRoutes(): void
    {
        $cached = $this->createGroupCountBasedRouter()->getCachedRoutes($this->createGroup());

        $router = $this->createGroupCountBasedRouter();
        $router->loadCachedRoutes($cached);

        $this->assertEquals(
            [RouterInterface::MATCH_FOUND, null, [], [self::class, 'postsIndex'], [], [], null],
            $router->match('GET', '/posts')
        );
    }

    /**
     * Test if routes are not loaded before use.
     */
    public function testRoutesAreNotLoaded2(): void
    {
        $this->expectException(LogicException::class);

        $this->createGroupCountBasedRouter()->match('GET', '/');
    }

    /**
     * Test compose URI for named route with parameters.
     *
     * @throws Exception
     */
    public function testRouterGetNamedRouteWithParams(): void
    {
        $router = $this->createGroupCountBasedRouter();
        $router->loadCachedRoutes($router->getCachedRoutes($this->createGroup()));

        $hostUri = 'http://server.foo';
        $this->assertEquals(
            'http://server.foo/posts/{id}',
            $router->get($hostUri, self::ROUTE_NAME_DELETE_POST)
        );
        $this->assertEquals(
            'http://server.foo/posts/123',
            $router->get($hostUri, self::ROUTE_NAME_DELETE_POST, ['id' => 123])
        );
    }

    /**
     * Test extract host URI from server request.
     *
     * @throws Exception
     */
    public function testGetHostUri(): void
    {
        $router = $this->createGroupCountBasedRouter();
        $router->loadCachedRoutes($router->getCachedRoutes($this->createGroup()));

        $request = new ServerRequest([], [], new Uri('http://server.foo/bla'));
        $this->assertEquals('http://server.foo', $router->getHostUri($request));
    }

    /**
     * @param RouterInterface $router
     *
     * @throws Exception
     */
    private function checkMatchRoutes(RouterInterface $router): void
    {
        $cachedRoutes = $router->getCachedRoutes($this->createGroup());
        $router->loadCachedRoutes($cachedRoutes);

        $this->assertEquals('/posts/{id:\d+}', $router->getUriPath(self::ROUTE_NAME_DELETE_POST));

        $this->assertEquals(
            [RouterInterface::MATCH_FOUND, null, [], [self::class, 'homeIndex'], [], [], null],
            $router->match('GET', '/')
        );

        $this->assertEquals(
            [RouterInterface::MATCH_FOUND, null, [], [self::class, 'postsIndex'], [], [], null],
            $router->match('GET', '/posts')
        );

        $this->assertEquals(
            [
                RouterInterface::MATCH_FOUND,
                null,
                [],
                [self::class, 'postsCreate'],
                [self::class . '::createPostMiddleware'],
                [self::class . '::createPostConfigurator'],
                [self::class, 'createRequest']
            ],
            $router->match('POST', '/posts')
        );

        $this->assertEquals(
            [RouterInterface::MATCH_NOT_FOUND, null, null, null, null, null, null],
            $router->match('GET', '/non-existent')
        );

        $this->assertEquals(
            [RouterInterface::MATCH_METHOD_NOT_ALLOWED, ['GET', 'POST'], null, null, null, null, null],
            $router->match('PATCH', '/')
        );
    }

    /**
     * @return GroupInterface
     *
     * @throws ReflectionException
     */
    private function createGroup(): GroupInterface
    {
        return (new Group([GroupInterface::PARAM_REQUEST_FACTORY => null]))
            ->get('/', [self::class, 'homeIndex'])
            ->group('posts', function (GroupInterface $group) {
                $group
                    ->get('', [self::class, 'postsIndex'])
                    ->post('', [self::class, 'postsCreate'], [
                        RouteInterface::PARAM_MIDDLEWARE_LIST         => [self::class . '::createPostMiddleware'],
                        RouteInterface::PARAM_CONTAINER_CONFIGURATORS => [self::class . '::createPostConfigurator'],
                        RouteInterface::PARAM_REQUEST_FACTORY         => [self::class, 'createRequest'],
                    ])
                    ->delete('{id:\d+}', [self::class, 'postsDelete'], [
                        RouteInterface::PARAM_NAME => self::ROUTE_NAME_DELETE_POST,
                    ]);
            })
            ->post('', [self::class, 'createNews']);
    }

    /**
     * @return RouterInterface
     */
    private function createGroupCountBasedRouter(): RouterInterface
    {
        return new Router(GroupCountBasedGenerator::class, GroupCountBased::class);
    }

    /**
     * @return RouterInterface
     */
    private function createGroupPosBasedRouter(): RouterInterface
    {
        return new Router(GroupPosBasedGenerator::class, GroupPosBased::class);
    }

    /**
     * @return RouterInterface
     */
    private function createCharCountBasedRouter(): RouterInterface
    {
        return new Router(CharCountBasedGenerator::class, CharCountBased::class);
    }

    /**
     * @return RouterInterface
     */
    private function createMarkBasedRouter(): RouterInterface
    {
        return new Router(MarkBasedGenerator::class, MarkBased::class);
    }

    /**
     * @return ResponseInterface
     */
    public static function homeIndex(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public static function createNews(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public static function postsIndex(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public static function postsCreate(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    /**
     * @param array $parameters
     *
     * @return ResponseInterface
     */
    public static function postsDelete(array $parameters): ResponseInterface
    {
        $parameters ?: null;

        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    /**
     * @param SapiInterface $sapi
     *
     * @return ServerRequestInterface
     */
    public static function createRequest(SapiInterface $sapi): ServerRequestInterface
    {
        $sapi ?: null;

        /** @var ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class);

        return $request;
    }

    /**
     * @return ResponseInterface
     */
    public static function createPostMiddleware(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        return $response;
    }

    public static function createPostConfigurator(): void
    {
        // dummy for tests
    }
}
