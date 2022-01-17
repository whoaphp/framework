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

namespace Whoa\JsonWebToken\Checkers;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\InvalidClaimException;

/**
 * @package Whoa\JsonWebToken
 */
class TenantChecker implements ClaimChecker
{
    /** @var string */
    private const CLAIM_NAME = 'tid';

    /**
     * @var string
     */
    private string $tenant;

    /**
     * @var bool
     */
    private bool $protectedHeader;

    /**
     * @param string $tenant
     * @param bool   $protectedHeader
     */
    public function __construct(string $tenant, bool $protectedHeader = false)
    {
        $this->tenant          = $tenant;
        $this->protectedHeader = $protectedHeader;
    }

    /**
     * @inheritDoc
     */
    public function checkClaim($value): void
    {
        $this->checkValue($value, InvalidClaimException::class);
    }

    /**
     * @inheritDoc
     */
    public function supportedClaim(): string
    {
        return self::CLAIM_NAME;
    }

    /**
     * @param        $value
     * @param string $class
     */
    private function checkValue($value, string $class): void
    {
        if (is_string($value) === true && $value !== $this->tenant) {
            throw new $class('Bad tenant.', self::CLAIM_NAME, $value);
        }
        if (is_array($value) === true && in_array($this->tenant, $value, true) === false) {
            throw new $class('Bad tenant.', self::CLAIM_NAME, $value);
        }
        if (is_array($value) === false && is_string($value) === false) {
            throw new $class('Bad tenant.', self::CLAIM_NAME, $value);
        }
    }
}
