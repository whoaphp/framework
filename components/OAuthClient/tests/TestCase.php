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

namespace Whoa\Tests\OAuthClient;

use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\None;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\PS384;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSBuilderFactory;
use Jose\Component\Signature\JWSLoaderFactory;
use Jose\Component\Signature\JWSVerifierFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer;
use Jose\Component\Signature\Serializer\JSONGeneralSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\JWSSerializerManagerFactory;
use Mockery;

/**
 * @package Whoa\Tests\OAuthClient
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AlgorithmManagerFactory|null
     */
    private ?AlgorithmManagerFactory $algorithmManagerFactory = null;

    /**
     * @var JWSBuilderFactory|null
     */
    private ?JWSBuilderFactory $jwsBuilderFactory = null;

    /**
     * @var JWSVerifierFactory|null
     */
    private ?JWSVerifierFactory $jwsVerifierFactory = null;

    /**
     * @var JWSSerializerManagerFactory|null
     */
    private ?JWSSerializerManagerFactory $jwsSerializerManagerFactory = null;

    /**
     * @var JWSSerializerManager|null
     */
    private ?JWSSerializerManager $jwsSerializerManager = null;

    /**
     * @var JWSLoaderFactory
     */
    private ?JWSLoaderFactory $jwsLoaderFactory = null;

    /**
     * @return AlgorithmManagerFactory
     */
    protected function getAlgorithmManagerFactory(): AlgorithmManagerFactory
    {
        if (null === $this->algorithmManagerFactory) {
            $this->algorithmManagerFactory = new AlgorithmManagerFactory();
            $this->algorithmManagerFactory->add('HS256', new HS256());
            $this->algorithmManagerFactory->add('HS384', new HS384());
            $this->algorithmManagerFactory->add('HS512', new HS512());
            $this->algorithmManagerFactory->add('ES256', new ES256());
            $this->algorithmManagerFactory->add('ES384', new ES384());
            $this->algorithmManagerFactory->add('ES512', new ES512());
            $this->algorithmManagerFactory->add('RS256', new RS256());
            $this->algorithmManagerFactory->add('RS384', new RS384());
            $this->algorithmManagerFactory->add('RS512', new RS512());
            $this->algorithmManagerFactory->add('PS256', new PS256());
            $this->algorithmManagerFactory->add('PS384', new PS384());
            $this->algorithmManagerFactory->add('PS512', new PS512());
            $this->algorithmManagerFactory->add('none', new None());
            $this->algorithmManagerFactory->add('EdDSA', new EdDSA());
        }

        return $this->algorithmManagerFactory;
    }

    /**
     * @return JWSBuilderFactory
     */
    protected function getJWSBuilderFactory(): JWSBuilderFactory
    {
        if (null === $this->jwsBuilderFactory) {
            $this->jwsBuilderFactory = new JWSBuilderFactory(
                $this->getAlgorithmManagerFactory()
            );
        }

        return $this->jwsBuilderFactory;
    }

    /**
     * @return JWSVerifierFactory
     */
    protected function getJWSVerifierFactory(): JWSVerifierFactory
    {
        if (null === $this->jwsVerifierFactory) {
            $this->jwsVerifierFactory = new JWSVerifierFactory(
                $this->getAlgorithmManagerFactory()
            );
        }

        return $this->jwsVerifierFactory;
    }

    /**
     * @return JWSSerializerManagerFactory
     */
    protected function getJWSSerializerManagerFactory(): JWSSerializerManagerFactory
    {
        if (null === $this->jwsSerializerManagerFactory) {
            $this->jwsSerializerManagerFactory = new JWSSerializerManagerFactory();
            $this->jwsSerializerManagerFactory->add(new CompactSerializer());
            $this->jwsSerializerManagerFactory->add(new JSONFlattenedSerializer());
            $this->jwsSerializerManagerFactory->add(new JSONGeneralSerializer());
        }

        return $this->jwsSerializerManagerFactory;
    }

    /**
     * @return JWSSerializerManager
     */
    protected function getJWSSerializerManager(): JWSSerializerManager
    {
        if (null === $this->jwsSerializerManager) {
            $this->jwsSerializerManager = new JWSSerializerManager([
                new CompactSerializer(),
                new JSONFlattenedSerializer(),
                new JSONGeneralSerializer(),
            ]);
        }

        return $this->jwsSerializerManager;
    }

    /**
     * @return JWSLoaderFactory
     */
    protected function getJWSLoaderFactory(): JWSLoaderFactory
    {
        if (null === $this->jwsLoaderFactory) {
            $this->jwsLoaderFactory = new JWSLoaderFactory(
                $this->getJWSSerializerManagerFactory(),
                $this->getJWSVerifierFactory(),
                null
            );
        }

        return $this->jwsLoaderFactory;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();;
    }
}
