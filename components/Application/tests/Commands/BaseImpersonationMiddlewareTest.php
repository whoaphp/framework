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

namespace Whoa\Tests\Application\Commands;

use Closure;
use Whoa\Application\Commands\BaseImpersonationMiddleware;
use Whoa\Container\Container;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\Commands\MiddlewareInterface;
use Whoa\Contracts\Passport\PassportAccountInterface;
use Whoa\Contracts\Settings\Packages\CommandSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Psr\Container\ContainerInterface;

/**
 * @package Whoa\Tests\Application
 */
class BaseImpersonationMiddlewareTest extends TestCase
{
    /**
     * Test middleware.
     */
    public function testMiddleware(): void
    {
        $container = $this->createContainer('123', ['prop1' => 'value1'], $passport);
        $middleware = $this->createMiddleware(['can_something' => true]);

        $inOut = Mockery::mock(IoInterface::class);
        /** @var IoInterface $inOut */

        $isNextCalled = false;
        $next = function () use (&$isNextCalled): void {
            $isNextCalled = true;
        };

        $middleware::handle($inOut, $next, $container);

        $this->assertTrue($isNextCalled);
        $this->assertNotNull($passport);

        /** @var PassportAccountInterface $passport */
        $this->assertTrue($passport->hasProperty('prop1'));
        $this->assertFalse($passport->hasProperty('prop2'));
        $this->assertEquals('value1', $passport->getProperty('prop1'));
        $this->assertTrue($passport->hasUserIdentity());
        $this->assertEquals('123', $passport->getUserIdentity());
        $this->assertFalse($passport->hasClientIdentity());
        $this->assertNull($passport->getClientIdentity());
        $this->assertTrue($passport->hasScopes());
        $this->assertTrue($passport->hasScope('can_something'));
        $this->assertEquals(['can_something' => true], $passport->getScopes());
    }

    /**
     * Create container with given user impersonation properties.
     *
     * @param string $identity
     * @param array $properties
     * @param PassportAccountInterface $passportSet
     *
     * @return ContainerInterface
     */
    private function createContainer(
        string                    $identity,
        array                     $properties,
        ?PassportAccountInterface &$passportSet
    ): ContainerInterface
    {
        $container = new Container();

        $container[SettingsProviderInterface::class] = $provider = Mockery::mock(SettingsProviderInterface::class);
        $provider
            ->shouldReceive('get')->once()
            ->with(CommandSettingsInterface::class)
            ->andReturn([
                CommandSettingsInterface::KEY_IMPERSONATE_AS_USER_IDENTITY => $identity,
                CommandSettingsInterface::KEY_IMPERSONATE_WITH_USER_PROPERTIES => $properties,
            ]);

        $container[AccountManagerInterface::class] = $manager = Mockery::mock(AccountManagerInterface::class);
        $manager
            ->shouldReceive('setAccount')->once()
            ->withAnyArgs()
            ->andReturnUsing(
                function (PassportAccountInterface $passport) use ($manager, &$passportSet): AccountManagerInterface {
                    $passportSet = $passport;

                    /** @var AccountManagerInterface $manager */
                    return $manager;
                }
            );

        return $container;
    }

    /**
     * @param array $scopes
     *
     * @return MiddlewareInterface
     */
    private function createMiddleware(array $scopes): MiddlewareInterface
    {
        $middleware = new class extends BaseImpersonationMiddleware {
            public static $scopes;

            /**
             * @inheritdoc
             */
            protected static function createReadScopesClosure(ContainerInterface $container): Closure
            {
                return function (): array {
                    return static::$scopes;
                };
            }
        };

        $middleware::$scopes = $scopes;

        return $middleware;
    }
}
