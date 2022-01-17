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

namespace Whoa\OAuthClient\Clients;

use GuzzleHttp\Exception\GuzzleException;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Whoa\JsonWebToken\Checkers\TenantChecker;
use Whoa\OAuthClient\Contracts\Clients\AzureV2Interface;
use Whoa\OAuthClient\Contracts\IdentityPlatform\IdentityPlatformInterface;
use Whoa\OAuthClient\Contracts\JsonWebToken\AzureV2JwtClaimInterface as JwtClaimInterface;
use Whoa\OAuthClient\Contracts\JsonWebToken\AzureV2JwtIdentityInterface as JwtIdentityInterface;
use Whoa\OAuthClient\Exceptions\InvalidArgumentException;
use Whoa\OAuthClient\Exceptions\RuntimeException;
use Whoa\OAuthClient\IdentityPlatform\IdentityPlatform;

/**
 * @package Whoa\OAuthClient
 */
class AzureV2 extends IdentityPlatform implements AzureV2Interface
{
    /**
     * @inheritDoc
     */
    public function getProviderIdentifier(): ?string
    {
        if (parent::getProviderIdentifier() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_PROVIDER);
        }

        return parent::getProviderIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function setProviderIdentifier(string $providerIdentifier): AzureV2Interface
    {
        return $this->setProviderIdentifierImpl($providerIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function getProviderName(): ?string
    {
        if (parent::getProviderName() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_PROVIDER_NAME);
        }

        return parent::getProviderName();
    }

    /**
     * @inheritDoc
     */
    public function setProviderName(string $providerName): AzureV2Interface
    {
        return $this->setProviderNameImpl($providerName);
    }

    /**
     * @inheritDoc
     */
    public function getClientIdentifier(): ?string
    {
        if (parent::getClientIdentifier() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_CLIENT_ID);
        }

        return parent::getClientIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function setClientIdentifier(string $clientIdentifier): AzureV2Interface
    {
        return $this->setClientIdentifierImpl($clientIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function getTenantIdentifier(): ?string
    {
        if (parent::getTenantIdentifier() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_TENANT_ID);
        }

        return parent::getTenantIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function setTenantIdentifier(string $tenantIdentifier): AzureV2Interface
    {
        return $this->setTenantIdentifierImpl($tenantIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function setDiscoveryDocumentUri(string $discoveryDocumentUri): AzureV2Interface
    {
        return $this->setDiscoveryDocumentUriImpl($discoveryDocumentUri);
    }

    /**
     * @inheritDoc
     */
    public function setJwkSetUriKey(string $jwkSetUriKey): AzureV2Interface
    {
        return $this->setJwkSetUriKeyImpl($jwkSetUriKey);
    }

    /**
     * @inheritDoc
     */
    public function setJwkSetUri(string $jwkSetUri): AzureV2Interface
    {
        return $this->setJwkSetUriImpl($jwkSetUri);
    }

    /**
     * @inheritDoc
     */
    public function setJwkUri(string $jwkUri): AzureV2Interface
    {
        return $this->setJwkUriImpl($jwkUri);
    }

    /**
     * @inheritDoc
     */
    public function getJwk(): ?JWKSet
    {
        if ($this->getDiscoveryDocumentUri() === null &&
            $this->getJwkSetUri() === null &&
            $this->getJwkUri() === null
        ) {
            if (parent::getJwk() == null) {
                throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWK_URIS);
            }
        }

        if ($this->getDiscoveryDocumentUri() !== null) {
            if ($this->getJwkSetUriKey() === null) {
                throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWK_SET_URI_KEY);
            }

            $jwkSet = $this->getJwkSetFromDiscoveryDocument();

            $this->setJwkSet($jwkSet);
        } else if ($this->getJwkSetUri() !== null) {
            $jwkSet = $this->getJwkSetFromUri();

            $this->setJwkSet($jwkSet);
        } else if ($this->getJwkUri() !== null) {
            $jwkSet = $this->getJwkFromUri();

            $this->setJwkSet($jwkSet);
        }

        if (parent::getJwk() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWK);
        }

        return parent::getJwk();
    }

    /**
     * @return JWKSet
     */
    private function getJwkSetFromDiscoveryDocument(): JWKSet
    {
        return $this->parseJwkSet($this->getJwkSetFromDiscoveryDocumentMetadata());
    }

    /**
     * @return string|null
     */
    private function getDiscoveryDocument(): ?string
    {
        try {
            $discoveryDocumentUri = $this->getDiscoveryDocumentUri();

            return $this->getDataFromUri($discoveryDocumentUri);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_LOAD_DISCOVERY_DOCUMENT, null, 400, [], null, $exception);
        }
    }

    /**
     * @return array|null
     */
    private function getDiscoveryDocumentMetadata(): ?array
    {
        try {
            $discoveryDocument = $this->getDiscoveryDocument();

            return $this->parseJsonArray($discoveryDocument);
        } catch (\JsonException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_PARSE_METADATA_DISCOVERY_DOCUMENT, null, 400, [], null, $exception);
        }
    }

    /**
     * @return JWKSet
     */
    private function getJwkSetFromDiscoveryDocumentMetadata(): JWKSet
    {
        try {
            $discoveryDocumentMetadata = $this->getDiscoveryDocumentMetadata();
            $jwkSetUriKey              = $this->getJwkSetUriKey();

            if (array_key_exists($jwkSetUriKey, $discoveryDocumentMetadata) === false) {
                throw new RuntimeException(RuntimeException::ERROR_UNDEFINED_JWK_SET_URI_KEY);
            }

            $jwkSetUri = $discoveryDocumentMetadata[$jwkSetUriKey];
            $jwtSet    = $this->getDataFromUri($jwkSetUri);

            return $this->parseJwkSet($jwtSet);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_LOAD_JWK_SET_METADATA, null, 400, [], null, $exception);
        }
    }

    /**
     * @return JWKSet
     */
    private function getJwkSetFromUri(): JWKSet
    {
        try {
            $jwkSetUri      = $this->getJwkSetUri();
            $jwkSetMetadata = $this->getDataFromUri($jwkSetUri);

            return $this->parseJwkSet($jwkSetMetadata);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_LOAD_JWK_SET_METADATA, null, 400, [], null, $exception);
        }
    }

    /**
     * @return JWKSet
     */
    private function getJwkFromUri(): JWKSet
    {
        try {
            $jwkUri      = $this->getJwkUri();
            $jwkMetadata = $this->getDataFromUri($jwkUri);

            return $this->parseJwk($jwkMetadata);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_LOAD_JWK_METADATA, null, 400, [], null, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function setJwkSet($keys): AzureV2Interface
    {
        return $this->setJwkImpl($this->parseJwkSet($keys));
    }

    /**
     * @inheritDoc
     */
    public function setJwk($values): AzureV2Interface
    {
        return $this->setJwkImpl($this->parseJwk($values));
    }

    /**
     * @inheritDoc
     */
    public function getMandatoryJwtHeaders(): array
    {
        if (empty(parent::getMandatoryJwtHeaders()) === true) {
            $this->setMandatoryJwtHeadersImpl(self::MANDATORY_JWT_HEADERS);
        }

        return parent::getMandatoryJwtHeaders();
    }

    /**
     * @inheritDoc
     */
    public function setMandatoryJwtHeaders(array $mandatoryJwtHeaders): AzureV2Interface
    {
        $modifiedMandatoryJwtHeaders = array_intersect(AzureV2Interface::MANDATORY_JWT_HEADERS, $mandatoryJwtHeaders);

        $this->setMandatoryJwtHeadersImpl($modifiedMandatoryJwtHeaders);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMandatoryJwtClaims(): array
    {
        if (empty(parent::getMandatoryJwtClaims()) === true) {
            $this->setMandatoryJwtClaimsImpl(self::MANDATORY_JWT_CLAIMS);
        }

        return parent::getMandatoryJwtClaims();
    }

    /**
     * @inheritDoc
     */
    public function setMandatoryJwtClaims(array $mandatoryJwtClaims): AzureV2Interface
    {
        $modifiedMandatoryJwtClaims = array_intersect(AzureV2Interface::MANDATORY_JWT_CLAIMS, $mandatoryJwtClaims);

        $this->setMandatoryJwtClaimsImpl($modifiedMandatoryJwtClaims);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function enableVerification(bool $enableVerification = true): AzureV2Interface
    {
        return $this->setEnableVerificationImpl($enableVerification);
    }

    /**
     * @inheritDoc
     */
    public function setSerializeJwt(string $serializeJwt): AzureV2Interface
    {
        return $this->setSerializeJwtImpl($serializeJwt);
    }

    /**
     * @inheritDoc
     */
    protected function getDeserializeJwt(): ?JWS
    {
        if ($this->getSerializeJwt() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_SERIALIZE_JWT);
        }

        try {
            $jwtLoader    = $this->getJwtLoader();
            $serializeJwt = $this->getSerializeJwt();

            $deserializeJwt = $jwtLoader->getSerializerManager()->unserialize($serializeJwt);
            $this->setDeserializeJwtImpl($deserializeJwt);

            return parent::getDeserializeJwt();
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_DESERIALIZE_JWT, null, 400, [], null, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function getJwt(int $format = IdentityPlatformInterface::KEY_DESERIALIZE_JWT)
    {
        assert($this->checkVerification());

        $serializeJwt   = $this->getSerializeJwt();
        $deserializeJwt = $this->getDeserializeJwt();

        if ($format === IdentityPlatformInterface::KEY_DESERIALIZE_JWT) {
            return $deserializeJwt;
        } else if ($format === IdentityPlatformInterface::KEY_SERIALIZE_JWT) {
            return $serializeJwt;
        } else {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_INVALID_JWT_FORMAT);
        }
    }

    /**
     * @inheritDoc
     */
    public function getJwtPayload(): ?array
    {
        $deserializeJwt = $this->getDeserializeJwt();
        $jwtPayload     = $deserializeJwt->getPayload();

        return $this->parseJwtPayload($jwtPayload);
    }

    /**
     * @inheritDoc
     */
    public function getJwtIdentities(): ?array
    {
        assert($this->checkVerification());

        $jwtPayload = $this->getJwtPayload();

        return [
            JwtIdentityInterface::KEY_PROVIDER_IDENTIFIER => $this->getProviderIdentifier(),
            JwtIdentityInterface::KEY_PROVIDER_NAME       => $this->getProviderName(),
            JwtIdentityInterface::KEY_TENANT_IDENTIFIER   => $this->getTenantIdentifier(),
            JwtIdentityInterface::KEY_CLIENT_IDENTIFIER   => $this->getClientIdentifier(),
            JwtIdentityInterface::KEY_USER_IDENTIFIER     => $jwtPayload[JwtClaimInterface::KEY_USER_IDENTIFIER],
            JwtIdentityInterface::KEY_USERNAME            => $jwtPayload[JwtClaimInterface::KEY_USERNAME],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function verifyJwtHeaders(): bool
    {
        $jwtLoader               = $this->getJwtLoader();
        $jwtHeaderCheckerManager = $jwtLoader->getHeaderCheckerManager();
        $deserializeJwt          = $this->getDeserializeJwt();
        $mandatoryJwtHeaders     = $this->getMandatoryJwtHeaders();

        $jwtHeaderCheckerManager->check($deserializeJwt, 0, $mandatoryJwtHeaders);

        return true;
    }

    /**
     * @inheritDoc
     * @throw \InvalidArgumentException
     */
    protected function verifyJwtKeys(): bool
    {
        $jwtLoader      = $this->getJwtLoader();
        $jwtVerifier    = $jwtLoader->getJwsVerifier();
        $deserializeJwt = $this->getDeserializeJwt();
        $jwk            = $this->getJwk();

        return $jwtVerifier->verifyWithKeySet($deserializeJwt, $jwk, 0);
    }

    /**
     * @inheritDoc
     * @throws MissingMandatoryClaimException
     * @throws InvalidClaimException
     */
    protected function verifyJwtClaims(): bool
    {
        $deserializeJwtPayload  = $this->getJwtPayload();
        $jwtClaimCheckerManager = $this->getClaimCheckManager();
        $mandatoryJwtClaims     = $this->getMandatoryJwtClaims();

        return empty($jwtClaimCheckerManager->check($deserializeJwtPayload, $mandatoryJwtClaims)) === false;
    }

    /**
     * @param string $jwtPayload
     *
     * @return array
     */
    private function parseJwtPayload(string $jwtPayload): array
    {
        try {
            return $this->parseJsonArray($jwtPayload);
        } catch (\JsonException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_PARSE_JWT_PAYLOAD, null, 400, [], null, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getJwtSerializers(): array
    {
        return $this->jwtSerializers = [
            new CompactSerializer(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getJwtAlgorithms(): array
    {
        return $this->jwtAlgorithms = [
            new RS256(),
            new HS256(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getJwtHeaderCheckers(): array
    {
        return $this->jwtHeaderCheckers = [
            new AlgorithmChecker([
                'RS256',
                'HS256'
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getJwtTokenTypeSupports(): array
    {
        return $this->jwtTokenTypeSupports = [
            new JWSTokenSupport(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getJwtClaimCheckers(): array
    {
        return $this->jwtClaimCheckers = [
            new IssuedAtChecker(),
            new NotBeforeChecker(),
            new ExpirationTimeChecker(),
            new AudienceChecker($this->getClientIdentifier()),
            new TenantChecker($this->getTenantIdentifier()),
        ];
    }

    /**
     * @return bool
     */
    private function checkVerification(): bool
    {
        if ($this->getEnableVerification() === true) {
            try {
                if (($verifyJwtHeaders = $this->verifyJwtHeaders()) === false) {
                    throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_HEADERS);
                }
            } catch (\InvalidArgumentException $exception) {
                throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_HEADERS, null, 400, [], null, $exception);
            }

            try {
                if (($verifyJwtKeys = $this->verifyJwtKeys()) === false) {
                    throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_KEYS);
                }
            } catch (\InvalidArgumentException $exception) {
                throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_KEYS, null, 400, [], null, $exception);
            }

            try {
                if (($verifyJwtClaims = $this->verifyJwtClaims()) === false) {
                    throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_CLAIMS);
                }
            } catch (MissingMandatoryClaimException $exception) {
                throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_CLAIMS, null, 400, [], null, $exception);
            } catch (InvalidClaimException $exception) {
                throw new RuntimeException(RuntimeException::ERROR_VERIFY_JWT_CLAIMS, null, 400, [], null, $exception);
            }

            return $verifyJwtHeaders === true && $verifyJwtKeys === true && $verifyJwtClaims === true;
        }

        return true;
    }
}
