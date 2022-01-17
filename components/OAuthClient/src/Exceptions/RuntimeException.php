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
class RuntimeException extends \RuntimeException implements WhoaExceptionInterface
{
    /** @var string */
    const ERROR_INVALID_REQUEST = 'error_invalid_request';

    /** @var string */
    const ERROR_LOAD_DISCOVERY_DOCUMENT = 'error_load_discovery_document';

    /** @var string */
    const ERROR_PARSE_METADATA_DISCOVERY_DOCUMENT = 'error_parse_metadata_discovery_document ';

    /** @var string */
    const ERROR_UNDEFINED_JWK_SET_URI_KEY = 'error_undefined_jwk_set_uri_key ';

    /** @var string */
    const ERROR_LOAD_JWK_SET_METADATA = 'error_load_jwk_set_metadata ';

    /** @var string */
    const ERROR_LOAD_JWK_METADATA = 'error_load_jwk_metadata ';

    /** @var string */
    const ERROR_DESERIALIZE_JWT = 'error_deserialize_jwt ';

    /** @var string */
    const ERROR_VERIFY_JWT_HEADERS = 'error_verify_jwt_headers ';

    /** @var string */
    const ERROR_VERIFY_JWT_KEYS = 'error_verify_jwt_keys ';

    /** @var string */
    const ERROR_VERIFY_JWT_CLAIMS = 'error_verify_jwt_claims ';

    /** @var string */
    const ERROR_PARSE_JWT_PAYLOAD = 'error_parse_jwt_payload ';

    /** @var string */
    const ERROR_PARSE_JWK_SET = 'error_parse_jwk_set ';

    /** @var string */
    const ERROR_PARSE_JWK = 'error_parse_jwk ';

    /** @var string[] */
    const DEFAULT_MESSAGES = [
        self::ERROR_INVALID_REQUEST                   => 'Invalid, unknown or unepxected request',
        self::ERROR_LOAD_DISCOVERY_DOCUMENT           => 'Unable to load Discovery Document.',
        self::ERROR_PARSE_METADATA_DISCOVERY_DOCUMENT => 'Unable to parse metadata from Discovery Document.',
        self::ERROR_LOAD_JWK_SET_METADATA             => 'Unable to load JWK Set metadata.',
        self::ERROR_LOAD_JWK_METADATA                 => 'Unable to load JWK metadata.',
        self::ERROR_UNDEFINED_JWK_SET_URI_KEY         => 'Undefined JWK Set URI key.',
        self::ERROR_DESERIALIZE_JWT                   => 'Unable to deserialize JWT.',
        self::ERROR_VERIFY_JWT_HEADERS                => 'Unable to verify JWT Header(s).',
        self::ERROR_VERIFY_JWT_KEYS                   => 'Unable to verify JWT Key(s).',
        self::ERROR_VERIFY_JWT_CLAIMS                 => 'Unable to verify JWT Claim(s).',
        self::ERROR_PARSE_JWT_PAYLOAD                 => 'Unable to parse JWT payload',
        self::ERROR_PARSE_JWK_SET                     => 'Unable to parse JWK Set.',
        self::ERROR_PARSE_JWK                         => 'Unable to parse JWK.',
    ];

    /**
     * @var string
     */
    private string $errorCode;

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

    /**
     * @param string         $errorCode
     * @param string|null    $errorUri
     * @param int            $httpCode
     * @param array          $httpHeaders
     * @param array|null     $descriptions
     * @param Exception|null $previous
     */
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
