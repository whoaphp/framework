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

namespace Whoa\OAuthClient\Traits;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\TokenTypeSupport;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\JWSSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Whoa\OAuthClient\Exceptions\InvalidArgumentException;

/**
 * @package Whoa\OAuthClient
 */
trait JwtTrait
{
    /**
     * @return JWSSerializer[]
     */
    abstract protected function getJwtSerializers(): array;

    /**
     * @return Algorithm[]
     */
    abstract protected function getJwtAlgorithms(): array;

    /**
     * @return HeaderChecker[]
     */
    abstract protected function getJwtHeaderCheckers(): array;

    /**
     * @return TokenTypeSupport[]
     */
    abstract protected function getJwtTokenTypeSupports(): array;

    /**
     * @return ClaimChecker[]
     */
    abstract protected function getJwtClaimCheckers(): array;

    /**
     * @return bool
     */
    abstract protected function verifyJwtHeaders(): bool;

    /**
     * @return bool
     */
    abstract protected function verifyJwtKeys(): bool;

    /**
     * @return bool
     */
    abstract protected function verifyJwtClaims(): bool;

    /**
     * @var JWSSerializer[]
     */
    protected array $jwtSerializers = [];

    /**
     * @var Algorithm[]
     */
    protected array $jwtAlgorithms = [];

    /**
     * @var HeaderChecker[]
     */
    protected array $jwtHeaderCheckers = [];

    /**
     * @var TokenTypeSupport[]
     */
    protected array $jwtTokenTypeSupports = [];

    /**
     * @var ClaimChecker[]
     */
    protected array $jwtClaimCheckers = [];

    /**
     * @var JWSSerializerManager|null
     */
    private ?JWSSerializerManager $jwtSerializerManager = null;

    /**
     * @var AlgorithmManager|null
     */
    private ?AlgorithmManager $jwtAlgorithmManager = null;

    /**
     * @var JWSVerifier|null
     */
    private ?JWSVerifier $jwtVerifier = null;

    /**
     * @var HeaderCheckerManager|null
     */
    private ?HeaderCheckerManager $jwtHeaderCheckerManager = null;

    /**
     * @var ClaimCheckerManager
     */
    private ?ClaimCheckerManager $jwtClaimCheckerManager = null;

    /**
     * @var JWSLoader|null
     */
    private ?JWSLoader $jwtLoader = null;

    /**
     * @return JWSSerializerManager
     */
    protected function getJwtSerializerManager(): JWSSerializerManager
    {
        if (empty($this->getJwtSerializers()) === true) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_SERIALIZERS);
        }

        if ($this->jwtSerializerManager === null)
            $this->jwtSerializerManager = new JWSSerializerManager($this->getJwtSerializers());

        return $this->jwtSerializerManager;
    }

    /**
     * @return AlgorithmManager
     */
    protected function getJwtAlgorithmManager(): AlgorithmManager
    {
        if (empty($this->getJwtAlgorithms()) === true) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_ALGORITHMS);
        }

        if ($this->jwtAlgorithmManager === null)
            $this->jwtAlgorithmManager = new AlgorithmManager($this->getJwtAlgorithms());

        return $this->jwtAlgorithmManager;
    }

    /**
     * @return JWSVerifier
     */
    protected function getJwtVerifier(): JWSVerifier
    {
        if ($this->getJwtAlgorithmManager() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_ALGORITHM_MANAGER);
        }

        if ($this->jwtVerifier === null)
            $this->jwtVerifier = new JWSVerifier($this->getJwtAlgorithmManager());

        return $this->jwtVerifier;
    }

    /**
     * @return HeaderCheckerManager
     */
    protected function getJwtHeaderCheckerManager(): HeaderCheckerManager
    {
        if (empty($this->getJwtHeaderCheckers()) === true) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_HEADER_CHECKERS);
        }

        if (empty($this->getJwtTokenTypeSupports()) === true) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_TOKEN_TYPE_SUPPORTS);
        }

        if ($this->jwtHeaderCheckerManager === null)
            $this->jwtHeaderCheckerManager = new HeaderCheckerManager(
                $this->getJwtHeaderCheckers(),
                $this->getJwtTokenTypeSupports()
            );

        return $this->jwtHeaderCheckerManager;
    }

    /**
     * @return ClaimCheckerManager
     */
    protected function getClaimCheckManager(): ClaimCheckerManager
    {
        if (empty($this->getJwtClaimCheckers()) === true) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_CLAIM_CHECKERS);
        }

        if ($this->jwtClaimCheckerManager === null)
            $this->jwtClaimCheckerManager = new ClaimCheckerManager($this->getJwtClaimCheckers());

        return $this->jwtClaimCheckerManager;
    }

    /**
     * @return JWSLoader
     */
    protected function getJwtLoader(): JWSLoader
    {
        if ($this->getJwtSerializerManager() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_SERIALIZER_MANAGER);
        }

        if ($this->getJwtVerifier() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_VERIFIER);
        }

        if ($this->getJwtHeaderCheckerManager() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_JWT_HEADER_CHECKER_MANAGER);
        }

        if ($this->jwtLoader === null) {
            $this->jwtLoader = new JWSLoader(
                $this->getJwtSerializerManager(),
                $this->getJwtVerifier(),
                $this->getJwtHeaderCheckerManager()
            );
        }

        return $this->jwtLoader;
    }
}
