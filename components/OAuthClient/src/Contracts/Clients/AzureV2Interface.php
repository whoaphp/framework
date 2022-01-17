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

namespace Whoa\OAuthClient\Contracts\Clients;

use Whoa\OAuthClient\Contracts\IdentityPlatform\IdentityPlatformInterface;
use Whoa\OAuthClient\Contracts\JsonWebToken\AzureV2JwtClaimInterface;
use Whoa\OAuthClient\Contracts\JsonWebToken\AzureV2JwtHeaderInterface;

/**
 * @package Whoa\OAuthClient
 */
interface AzureV2Interface extends IdentityPlatformInterface
{
    /** @var string[] */
    const MANDATORY_JWT_CLAIMS = [
        AzureV2JwtClaimInterface::KEY_USER_IDENTIFIER,
        AzureV2JwtClaimInterface::KEY_ISSUED_AT,
        AzureV2JwtClaimInterface::KEY_NOT_BEFORE,
        AzureV2JwtClaimInterface::KEY_EXPIRATION_TIME,
        AzureV2JwtClaimInterface::KEY_AUDIENCE,
        AzureV2JwtClaimInterface::KEY_TENANT_IDENTIFIER,
        AzureV2JwtClaimInterface::KEY_USERNAME,
    ];

    const MANDATORY_JWT_HEADERS = [
        AzureV2JwtHeaderInterface::KEY_PUBLIC_KEY_THUMBPRINT,
    ];

    /**
     * @param string $providerIdentifier
     *
     * @return AzureV2Interface
     */
    public function setProviderIdentifier(string $providerIdentifier): AzureV2Interface;

    /**
     * @param string $providerName
     *
     * @return AzureV2Interface
     */
    public function setProviderName(string $providerName): AzureV2Interface;

    /**
     * @param string $clientIdentifier
     *
     * @return AzureV2Interface
     */
    public function setClientIdentifier(string $clientIdentifier): AzureV2Interface;

    /**
     * @param string $tenantIdentifier
     *
     * @return AzureV2Interface
     */
    public function setTenantIdentifier(string $tenantIdentifier): AzureV2Interface;

    /**
     * @param string $discoveryDocumentUri
     *
     * @return AzureV2Interface
     */
    public function setDiscoveryDocumentUri(string $discoveryDocumentUri): AzureV2Interface;

    /**
     * @param string $jwkSetUriKey
     *
     * @return AzureV2Interface
     */
    public function setJwkSetUriKey(string $jwkSetUriKey): AzureV2Interface;

    /**
     * @param string $jwkSetUri
     *
     * @return AzureV2Interface
     */
    public function setJwkSetUri(string $jwkSetUri): AzureV2Interface;

    /**
     * @param string $jwkUri
     *
     * @return AzureV2Interface
     */
    public function setJwkUri(string $jwkUri): AzureV2Interface;

    /**
     * @param $keys
     *
     * @return AzureV2Interface
     */
    public function setJwkSet($keys): AzureV2Interface;

    /**
     * @param $values
     *
     * @return AzureV2Interface
     */
    public function setJwk($values): AzureV2Interface;

    /**
     * @param array $mandatoryJwtHeaders
     *
     * @return AzureV2Interface
     */
    public function setMandatoryJwtHeaders(array $mandatoryJwtHeaders): AzureV2Interface;

    /**
     * @param array $mandatoryJwtClaims
     *
     * @return AzureV2Interface
     */
    public function setMandatoryJwtClaims(array $mandatoryJwtClaims): AzureV2Interface;

    /**
     * @param bool $enableVerification
     *
     * @return AzureV2Interface
     */
    public function enableVerification(bool $enableVerification = true): AzureV2Interface;

    /**
     * @param string $serializeJwt
     *
     * @return AzureV2Interface
     */
    public function setSerializeJwt(string $serializeJwt): AzureV2Interface;

    /**
     * @param int $format
     *
     * @return mixed
     */
    public function getJwt(int $format = IdentityPlatformInterface::KEY_DESERIALIZE_JWT);

    /**
     * @return array|null
     */
    public function getJwtPayload(): ?array;

    /**
     * @return array|null
     */
    public function getJwtIdentities(): ?array;
}
