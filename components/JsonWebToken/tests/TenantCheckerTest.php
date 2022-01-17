<?php

/**
 * Copyright 2021-2022 info@whoaphp.com
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

namespace Whoa\Tests\JsonWebToken;

use Jose\Component\Checker\InvalidClaimException;
use Whoa\JsonWebToken\Checkers\TenantChecker;

/**
 * @package Whoa\Tests\JsonWebToken
 */
class TenantCheckerTest extends TestCase
{
    /**
     * Test tenant claim 1
     */
    public function testTenantClaim1(): void
    {
        $this->expectException(InvalidClaimException::class);
        $this->expectExceptionMessage('Bad tenant.');

        $checker = new TenantChecker('foo');
        $checker->checkClaim(1);
    }

    /**
     * Test tenant claim 2
     */
    public function testTenantClaim2(): void
    {
        $this->expectException(InvalidClaimException::class);
        $this->expectExceptionMessage('Bad tenant.');

        $checker = new TenantChecker('foo');
        $checker->checkClaim('bar');
    }

    /**
     * Test tenant claim 3
     */
    public function testTenantClaim3(): void
    {
        $this->expectException(InvalidClaimException::class);
        $this->expectExceptionMessage('Bad tenant.');

        $checker = new TenantChecker('foo');
        $checker->checkClaim(['bar']);
    }

    /**
     * Test tenant claim 4
     *
     * @throws InvalidClaimException
     */
    public function testTenantClaim4(): void
    {
        $checker = new TenantChecker('foo');
        $checker->checkClaim('foo');
        $checker->checkClaim(['foo']);
        static::assertEquals('tid', $checker->supportedClaim());
    }
}
