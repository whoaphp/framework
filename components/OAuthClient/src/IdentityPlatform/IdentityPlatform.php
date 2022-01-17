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

declare(strict_types=1);

namespace Whoa\OAuthClient\IdentityPlatform;

use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\JWS;
use Whoa\OAuthClient\Contracts\IdentityPlatform\IdentityPlatformInterface;
use Whoa\OAuthClient\Traits\HttpClientTrait;
use Whoa\OAuthClient\Traits\JsonTrait;
use Whoa\OAuthClient\Traits\JwkTrait;
use Whoa\OAuthClient\Traits\JwtTrait;
use Whoa\OAuthClient\Traits\UriTrait;

/**
 * @package Whoa\OAuthClient
 */
abstract class IdentityPlatform implements IdentityPlatformInterface
{
    use JwtTrait, JwkTrait, HttpClientTrait, UriTrait, JsonTrait;

    /**
     * @var string|null
     */
    private ?string $providerIdentifier = null;

    /**
     * @var string|null
     */
    private ?string $providerName = null;

    /**
     * @var string|null
     */
    private ?string $clientIdentifier = null;

    /**
     * @var string|null
     */
    private ?string $tenantIdentifier = null;

    /**
     * @var string|null
     */
    private ?string $discoveryDocumentUri = null;

    /**
     * @var string|null
     */
    private ?string $jwkSetUriKey = null;

    /**
     * @var string|null
     */
    private ?string $jwkSetUri = null;

    /**
     * @var string|null
     */
    private ?string $jwkUri = null;

    /**
     * @var JWKSet|null
     */
    private ?JWKSet $jwk = null;

    /**
     * @var array
     */
    private array $mandatoryJwtHeaders = [];

    /**
     * @var array
     */
    private array $mandatoryJwtClaims = [];

    /**
     * @var bool
     */
    private bool $enableVerification = true;

    /**
     * @var string|null
     */
    private ?string $serializeJwt = null;

    /**
     * @var JWS|null
     */
    private ?JWS $deserializeJwt = null;

    /**
     * @inheritDoc
     */
    public function getProviderIdentifier(): ?string
    {
        return $this->providerIdentifier;
    }

    /**
     * @param string $providerIdentifier
     *
     * @return IdentityPlatformInterface
     */
    protected function setProviderIdentifierImpl(string $providerIdentifier): IdentityPlatformInterface
    {
        $this->providerIdentifier = $providerIdentifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     *
     * @return IdentityPlatformInterface
     */
    protected function setProviderNameImpl(string $providerName): IdentityPlatformInterface
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClientIdentifier(): ?string
    {
        return $this->clientIdentifier;
    }

    /**
     * @param string $clientIdentifier
     *
     * @return IdentityPlatformInterface
     */
    protected function setClientIdentifierImpl(string $clientIdentifier): IdentityPlatformInterface
    {
        $this->clientIdentifier = $clientIdentifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTenantIdentifier(): ?string
    {
        return $this->tenantIdentifier;
    }

    /**
     * @param string $tenantIdentifier
     *
     * @return IdentityPlatformInterface
     */
    protected function setTenantIdentifierImpl(string $tenantIdentifier): IdentityPlatformInterface
    {
        $this->tenantIdentifier = $tenantIdentifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDiscoveryDocumentUri(): ?string
    {
        return $this->discoveryDocumentUri;
    }

    /**
     * @param string $discoveryDocumentUri
     *
     * @return IdentityPlatformInterface
     */
    protected function setDiscoveryDocumentUriImpl(string $discoveryDocumentUri): IdentityPlatformInterface
    {
        if ($this->isValidUri($discoveryDocumentUri) === true) {
            $this->discoveryDocumentUri = $discoveryDocumentUri;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getJwkSetUriKey(): ?string
    {
        return $this->jwkSetUriKey;
    }

    /**
     * @param string $jwkSetUriKey
     *
     * @return IdentityPlatformInterface
     */
    public function setJwkSetUriKeyImpl(string $jwkSetUriKey): IdentityPlatformInterface
    {
        $this->jwkSetUriKey = $jwkSetUriKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getJwkSetUri(): ?string
    {
        return $this->jwkSetUri;
    }

    /**
     * @param string $jwkSetUri
     *
     * @return IdentityPlatformInterface
     */
    protected function setJwkSetUriImpl(string $jwkSetUri): IdentityPlatformInterface
    {
        if ($this->isValidUri($jwkSetUri) === true) {
            $this->jwkSetUri = $jwkSetUri;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getJwkUri(): ?string
    {
        return $this->jwkUri;
    }

    /**
     * @param string $jwkUri
     *
     * @return IdentityPlatformInterface
     */
    protected function setJwkUriImpl(?string $jwkUri): IdentityPlatformInterface
    {
        if ($this->isValidUri($jwkUri) === true) {
            $this->jwkUri = $jwkUri;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getJwk(): ?JWKSet
    {
        return $this->jwk;
    }

    /**
     * @param JWKSet $jwk
     *
     * @return IdentityPlatformInterface
     */
    protected function setJwkImpl(JWKSet $jwk): IdentityPlatformInterface
    {
        $this->jwk = $jwk;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMandatoryJwtHeaders(): array
    {
        return $this->mandatoryJwtHeaders;
    }

    /**
     * @param array $mandatoryJwtHeaders
     *
     * @return IdentityPlatformInterface
     */
    protected function setMandatoryJwtHeadersImpl(array $mandatoryJwtHeaders): IdentityPlatformInterface
    {
        $this->mandatoryJwtHeaders = $mandatoryJwtHeaders;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMandatoryJwtClaims(): array
    {
        return $this->mandatoryJwtClaims;
    }

    /**
     * @param array $mandatoryJwtClaims
     *
     * @return IdentityPlatformInterface
     */
    protected function setMandatoryJwtClaimsImpl(array $mandatoryJwtClaims): IdentityPlatformInterface
    {
        $this->mandatoryJwtClaims = $mandatoryJwtClaims;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getEnableVerification(): bool
    {
        return $this->enableVerification;
    }

    /**
     * @param bool $enableVerification
     *
     * @return IdentityPlatformInterface
     */
    protected function setEnableVerificationImpl(bool $enableVerification): IdentityPlatformInterface
    {
        $this->enableVerification = $enableVerification;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSerializeJwt(): string
    {
        return $this->serializeJwt;
    }

    /**
     * @param string $serializeJwt
     *
     * @return IdentityPlatformInterface
     */
    protected function setSerializeJwtImpl(string $serializeJwt): IdentityPlatformInterface
    {
        $this->serializeJwt = $serializeJwt;

        return $this;
    }

    /**
     * @return JWS|null
     */
    protected function getDeserializeJwt(): ?JWS
    {
        return $this->deserializeJwt;
    }

    /**
     * @param JWS $deserializeJwt
     *
     * @return IdentityPlatformInterface
     */
    protected function setDeserializeJwtImpl(JWS $deserializeJwt): IdentityPlatformInterface
    {
        $this->deserializeJwt = $deserializeJwt;

        return $this;
    }
}
