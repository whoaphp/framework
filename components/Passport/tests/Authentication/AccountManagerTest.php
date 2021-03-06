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

namespace Whoa\Tests\Passport\Authentication;

use Exception;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Passport\Authentication\AccountManager;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Passport\Authentication\PassportAccount;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Entities\DatabaseSchema;
use Whoa\Passport\Package\PassportSettings;
use Whoa\Tests\Passport\Data\TestContainer;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class AccountManagerTest extends TestCase
{
    /**
     * Test get and set.
     *
     * @throws Exception
     */
    public function testGetSet()
    {
        $container = new TestContainer();

        /** @var AccountManagerInterface $manager */
        $manager = new AccountManager($container);
        $this->assertNull($manager->getAccount());

        /** @var DatabaseSchemaInterface $schemaMock */
        $schemaMock = Mockery::mock(DatabaseSchemaInterface::class);
        $passport   = new PassportAccount($schemaMock);

        $this->assertSame($passport, $manager->setAccount($passport)->getAccount());
    }

    /**
     * Test setting current account with token value.
     *
     * @throws Exception
     */
    public function testSetAccountWithTokenValue()
    {
        $container = new TestContainer();

        /** @var Mock $repoMock */
        /** @var Mock $providerMock */
        $container[TokenRepositoryInterface::class]  = $repoMock = Mockery::mock(TokenRepositoryInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock = Mockery::mock(SettingsProviderInterface::class);
        $container[DatabaseSchemaInterface::class]   = $schema = new DatabaseSchema();

        $timeout    = 3600;
        $tokenValue = '123';

        $providerMock->shouldReceive('get')->once()->with(PassportSettings::class)->andReturn([
            PassportSettings::KEY_TOKEN_EXPIRATION_TIME_IN_SECONDS => $timeout,
        ]);

        $properties = [
            $schema->getTokensUserIdentityColumn()   => $userId = '123',
            $schema->getTokensClientIdentityColumn() => $clientId = 'some_client_id',
            $schema->getTokensViewScopesColumn()     => [
                $scope1 = 'some_scope_1',
            ],
        ];
        $repoMock->shouldReceive('readPassport')->once()->with($tokenValue, $timeout)->andReturn($properties);

        $account = (new AccountManager($container))->setAccountWithTokenValue($tokenValue);

        $this->assertTrue($account->hasUserIdentity());
        $this->assertEquals($userId, $account->getUserIdentity());
        $this->assertTrue($account->hasClientIdentity());
        $this->assertEquals($clientId, $account->getClientIdentity());
        $this->assertTrue($account->hasScope($scope1));
        $this->assertFalse($account->hasScope($scope1 . 'XXX'));
    }

    /**
     * Test setting current account with invalid token value.
     */
    public function testSetAccountWithInvalidTokenValue()
    {
        $this->expectException(\Whoa\Passport\Exceptions\AuthenticationException::class);

        $container = new TestContainer();

        /** @var Mock $repoMock */
        /** @var Mock $providerMock */
        $container[TokenRepositoryInterface::class]  = $repoMock = Mockery::mock(TokenRepositoryInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock = Mockery::mock(SettingsProviderInterface::class);

        $timeout    = 3600;
        $tokenValue = '123';

        $providerMock->shouldReceive('get')->once()->with(PassportSettings::class)->andReturn([
            PassportSettings::KEY_TOKEN_EXPIRATION_TIME_IN_SECONDS => $timeout,
        ]);

        $properties = null;
        $repoMock->shouldReceive('readPassport')->once()->with($tokenValue, $timeout)->andReturn($properties);

        (new AccountManager($container))->setAccountWithTokenValue($tokenValue);
    }
}
