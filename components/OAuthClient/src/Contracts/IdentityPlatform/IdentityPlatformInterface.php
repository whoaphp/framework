<?php

/**
 * Copyright 2021 info@whoaphp.com
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

declare (strict_types=1);

namespace Whoa\OAuthClient\Contracts\IdentityPlatform;

use Jose\Component\Core\JWKSet;

/**
 * @package Whoa\OAuthClient
 */
interface IdentityPlatformInterface
{
    /** @var int Key name */
    const KEY_SERIALIZE_JWT = 0;

    /** @var int Key name */
    const KEY_DESERIALIZE_JWT = self::KEY_SERIALIZE_JWT + 1;

    /** @var int Key name */
    const KEY_LAST = self::KEY_DESERIALIZE_JWT + 1;

    /**
     * @return string|null
     */
    public function getProviderIdentifier(): ?string;

    /**
     * @return string|null
     */
    public function getProviderName(): ?string;

    /**
     * @return string|null
     */
    public function getClientIdentifier(): ?string;

    /**
     * @return string|null
     */
    public function getTenantIdentifier(): ?string;

    /**
     * @return string|null
     */
    public function getDiscoveryDocumentUri(): ?string;

    /**
     * @return string|null
     */
    public function getJwkSetUriKey(): ?string;

    /**
     * @return string|null
     */
    public function getJwkSetUri(): ?string;

    /**
     * @return string|null
     */
    public function getJwkUri(): ?string;

    /**
     * @return JWKSet|null
     */
    public function getJwk(): ?JWKSet;

    /**
     * @return array
     */
    public function getMandatoryJwtHeaders(): array;

    /**
     * @return array
     */
    public function getMandatoryJwtClaims(): array;

    /**
     * @return bool
     */
    public function getEnableVerification(): bool;

    /**
     * @return string|null
     */
    public function getSerializeJwt(): ?string;
}
