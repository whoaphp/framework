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

namespace Whoa\OAuthClient\Exceptions;

use Exception;
use Whoa\Contracts\Exceptions\WhoaExceptionInterface;

/**
 * @package Whoa\OAuthClient
 */
class InvalidArgumentException extends \InvalidArgumentException implements WhoaExceptionInterface
{
    /** @var string */
    const ERROR_INVALID_REQUEST = 'error_invalid_request';

    /** @var string */
    const ERROR_MISSING_PROVIDER = 'error_missing_provider';

    /** @var string */
    const ERROR_MISSING_PROVIDER_NAME = 'error_missing_provider_name';

    /** @var string */
    const ERROR_MISSING_CLIENT_ID = 'error_missing_client_id';

    /** @var string */
    const ERROR_MISSING_TENANT_ID = 'error_missing_tenant_id';

    /** @var string */
    const ERROR_MISSING_JWK_URIS = 'error_missing_jwk_uris';

    /** @var string */
    const ERROR_MISSING_JWK_SET_URI_KEY = 'error_missing_jwk_set_uri_key';

    /** @var string */
    const ERROR_MISSING_JWK = 'error_missing_jwk';

    /** @var string */
    const ERROR_MISSING_SERIALIZE_JWT = 'error_missing_serialize_jwt';

    /** @var string */
    const ERROR_INVALID_JWT_FORMAT = 'error_invalid_jwt_format';

    /** @var string */
    const ERROR_MISSING_HTTP_CLIENT = 'error_missing_http_client';

    /** @var int */
    const ERROR_INVALID_URI = 'error_invalid_uri';

    /** @var string */
    const ERROR_MISSING_JWT_SERIALIZERS = 'error_missing_jwt_serializers';

    /** @var string */
    const ERROR_MISSING_JWT_ALGORITHMS = 'error_missing_jwt_algorithms';

    /** @var string */
    const ERROR_MISSING_JWT_ALGORITHM_MANAGER = 'error_missing_jwt_algorithm_manager';

    /** @var string */
    const ERROR_MISSING_JWT_HEADER_CHECKERS = 'error_missing_jwt_header_checkers';

    /** @var string */
    const ERROR_MISSING_JWT_TOKEN_TYPE_SUPPORTS = 'error_missing_jwt_token_type_supports';

    /** @var string */
    const ERROR_MISSING_JWT_CLAIM_CHECKERS = 'error_missing_jwt_claim_checkers';

    /** @var string */
    const ERROR_MISSING_JWT_SERIALIZER_MANAGER = 'error_missing_jwt_serializer_manager';

    /** @var string */
    const ERROR_MISSING_JWT_VERIFIER = 'error_missing_jwt_verifier';

    /** @var string */
    const ERROR_MISSING_JWT_HEADER_CHECKER_MANAGER = 'error_missing_jwt_header_jwt_header_manager';

    /** @var string[] */
    const DEFAULT_MESSAGES = [
        self::ERROR_INVALID_REQUEST                    => 'Invalid, unknown or unepxected request',
        self::ERROR_MISSING_PROVIDER                   => 'Missing Provider.',
        self::ERROR_MISSING_PROVIDER_NAME              => 'Missing Provider Name.',
        self::ERROR_MISSING_CLIENT_ID                  => 'Missing Client Id.',
        self::ERROR_MISSING_TENANT_ID                  => 'Missing Tenant Id.',
        self::ERROR_MISSING_JWK_URIS                   => 'Missing available endpoint URI(s), either Discovery Document, JWK Set, or JWK.',
        self::ERROR_MISSING_JWK_SET_URI_KEY            => 'Missing JWK URI key for Discovery Document.',
        self::ERROR_MISSING_JWK                        => 'Missing JWK.',
        self::ERROR_MISSING_SERIALIZE_JWT              => 'Missing serialize JWT.',
        self::ERROR_INVALID_JWT_FORMAT                 => 'Invalid JWT format.',
        self::ERROR_MISSING_HTTP_CLIENT                => 'Missing HTTP client.',
        self::ERROR_INVALID_URI                        => 'Invalid or malformed URI.',
        self::ERROR_MISSING_JWT_SERIALIZERS            => 'Missing available JWT Serializer(s).',
        self::ERROR_MISSING_JWT_ALGORITHMS             => 'Missing available JWT Algorithm(s).',
        self::ERROR_MISSING_JWT_ALGORITHM_MANAGER      => 'Missing available JWT Algorithm Manager.',
        self::ERROR_MISSING_JWT_HEADER_CHECKERS        => 'Missing available JWT Header Checker(s).',
        self::ERROR_MISSING_JWT_TOKEN_TYPE_SUPPORTS    => 'Missing available JWT Type Support(s).',
        self::ERROR_MISSING_JWT_CLAIM_CHECKERS         => 'Missing available JWT Claim Checker(s).',
        self::ERROR_MISSING_JWT_SERIALIZER_MANAGER     => 'Missing available JWT Serializer Manager.',
        self::ERROR_MISSING_JWT_VERIFIER               => 'Missing available JWT Verifier.',
        self::ERROR_MISSING_JWT_HEADER_CHECKER_MANAGER => 'Missing available JWT Header Checker Manager.',
    ];

    /**
     * @var string
     */
    private ?string $errorCode;

    /**
     * @var int
     */
    private int $httpCode;

    /**
     * @var string[]
     */
    private array $httpHeaders;

    /**
     * @var string|null
     */
    private ?string $errorUri;

    public function __construct(
        string $errorCode,
        string $errorUri = null,
        int $httpCode = 400,
        array $httpHeaders = [],
        array $descriptions = null,
        Exception $previous = null
    )
    {
        $descriptions = $descriptions === null ? self::DEFAULT_MESSAGES : $descriptions;

        if ($previous === null) {
            parent::__construct($descriptions[$errorCode], 0, $previous);
        } else {
            parent::__construct("$descriptions[$errorCode] ({$previous->getMessage()})", 0, $previous);
        }

        $this->errorCode   = $errorCode;
        $this->errorUri    = $errorUri;
        $this->httpCode    = $httpCode;
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription(): string
    {
        return $this->getMessage();
    }

    /**
     * @return string|null
     */
    public function getErrorUri(): ?string
    {
        return $this->errorUri;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string[]
     */
    public function getHttpHeaders(): array
    {
        return $this->httpHeaders;
    }
}
