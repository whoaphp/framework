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

namespace Whoa\Tests\OAuthClient;

use Exception;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Easy\Build;
use Whoa\OAuthClient\Contracts\JsonWebToken\AzureV2JwtClaimInterface;
use Whoa\OAuthClient\Exceptions\InvalidArgumentException;
use Whoa\OAuthClient\Clients\AzureV2;
use Whoa\OAuthClient\Contracts\IdentityPlatform\IdentityPlatformInterface;
use Whoa\OAuthClient\Exceptions\RuntimeException;
use Whoa\Tests\OAuthClient\Settings\AzureV2 as S;

/**
 * @package Whoa\Tests\OAuthClient
 */
class AzureV2Test extends TestCase
{
    /**
     * Test valid JWT
     */
    public function testValidJsonWebTokenSettings(): void
    {
        [$jwk, $serializeJwt] = $this->createValidJwt();

        $azureV2 = (new AzureV2())
//            ->setProviderIdentifier(S::PROVIDER_IDENTIFIER)
//            ->setProviderName(S::PROVIDER_NAME)
            ->setClientIdentifier(S::CLIENT_IDENTIFIER)
            ->setTenantIdentifier(S::TENANT_IDENTIFIER)
            ->setJwk($jwk)
            ->setMandatoryJwtClaims([
                AzureV2JwtClaimInterface::KEY_ISSUED_AT,
                AzureV2JwtClaimInterface::KEY_NOT_BEFORE,
                AzureV2JwtClaimInterface::KEY_EXPIRATION_TIME,
                AzureV2JwtClaimInterface::KEY_AUDIENCE,
                AzureV2JwtClaimInterface::KEY_TENANT_IDENTIFIER,
            ])
            ->setSerializeJwt($serializeJwt);

        $this->assertEquals(S::CLIENT_IDENTIFIER, $azureV2->getClientIdentifier());
        $this->assertEquals(S::TENANT_IDENTIFIER, $azureV2->getTenantIdentifier());
        $this->assertEquals(new JWKSet([$jwk]), $azureV2->getJwk());
        $this->assertEquals($serializeJwt, $azureV2->getJwt(IdentityPlatformInterface::KEY_SERIALIZE_JWT));
        $this->assertNotNull($azureV2->getJwtIdentities());
    }

    /**
     * Test invalid JWT
     */
    public function testInvalidJwt(): void
    {
        [, $serializeJwt] = $this->createValidJwt();

        $azureV2 = new AzureV2();

        try {
            $azureV2->getClientIdentifier();
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertEquals(InvalidArgumentException::ERROR_MISSING_CLIENT_ID, $exception->getErrorCode());
        }

        try {
            $azureV2->setClientIdentifier(S::CLIENT_IDENTIFIER);
            $azureV2->getTenantIdentifier();
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertEquals(InvalidArgumentException::ERROR_MISSING_TENANT_ID, $exception->getErrorCode());
        }

        try {
            $azureV2->setTenantIdentifier(S::TENANT_IDENTIFIER);
            $azureV2->getJwk();
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertEquals(InvalidArgumentException::ERROR_MISSING_JWK_URIS, $exception->getErrorCode());
        }

        try {
            $azureV2->setDiscoveryDocumentUri(S::INVALID_DISCOVERY_DOCUMENT_URI);
            $azureV2->getJwk();
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertEquals(InvalidArgumentException::ERROR_INVALID_URI, $exception->getErrorCode());
        }

        try {
            $azureV2->setDiscoveryDocumentUri(S::VALID_DISCOVERY_DOCUMENT_URI);
            $azureV2->getJwk();
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertEquals(InvalidArgumentException::ERROR_MISSING_JWK_SET_URI_KEY, $exception->getErrorCode());
        }

        try {
            $azureV2->setJwkSetUriKey(S::JSON_WEB_KEY_SET_ARRAY_KEY);
            $azureV2->getJwk();
        } catch (Exception $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertEquals(RuntimeException::ERROR_LOAD_DISCOVERY_DOCUMENT, $exception->getErrorCode());
        }

//        try {
//            $azure2->setJsonWebKeySetArrayKey(S::VALID_DISCOVERY_DOCUMENT_URI);
//            $azure2->getJsonWebKeySet();
//        } catch (Exception $exception) {
//            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
//            $this->assertEquals('Failed to retrieve array key of JSON Web Key Set URI.', $exception->getMessage());
//        }

//        $this->assertEquals(S::CLIENT_ID, $azure2->getClientId());
//        $this->assertEquals(S::TENANT_ID, $azure2->getTenantId());
//
//        try {
//            $azure2->getJsonWebKeySet();
//        } catch (Exception $exception) {
//            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
//        }
//
//        $azure2->set
    }

    /**
     * @return array
     */
    private function createValidJwt(): array
    {
        $jwk = new JWK([
            'kty' => 'RSA',
            'kid' => 'bilbo.baggins@hobbiton.example',
            'use' => 'sig',
            'n'   => 'n4EPtAOCc9AlkeQHPzHStgAbgs7bTZLwUBZdR8_KuKPEHLd4rHVTeT-O-XV2jRojdNhxJWTDvNd7nqQ0VEiZQHz_AJmSCpMaJMRBSFKrKb2wqVwGU_NsYOYL-QtiWN2lbzcEe6XC0dApr5ydQLrHqkHHig3RBordaZ6Aj-oBHqFEHYpPe7Tpe-OfVfHd1E6cS6M1FZcD1NNLYD5lFHpPI9bTwJlsde3uhGqC0ZCuEHg8lhzwOHrtIQbS0FVbb9k3-tVTU4fg_3L_vniUFAKwuCLqKnS2BYwdq_mzSnbLY7h_qixoR7jig3__kRhuaxwUkRz5iaiQkqgc5gHdrNP5zw',
            'e'   => 'AQAB',
            'd'   => 'bWUC9B-EFRIo8kpGfh0ZuyGPvMNKvYWNtB_ikiH9k20eT-O1q_I78eiZkpXxXQ0UTEs2LsNRS-8uJbvQ-A1irkwMSMkK1J3XTGgdrhCku9gRldY7sNA_AKZGh-Q661_42rINLRCe8W-nZ34ui_qOfkLnK9QWDDqpaIsA-bMwWWSDFu2MUBYwkHTMEzLYGqOe04noqeq1hExBTHBOBdkMXiuFhUq1BU6l-DqEiWxqg82sXt2h-LMnT3046AOYJoRioz75tSUQfGCshWTBnP5uDjd18kKhyv07lhfSJdrPdM5Plyl21hsFf4L_mHCuoFau7gdsPfHPxxjVOcOpBrQzwQ',
            'p'   => '3Slxg_DwTXJcb6095RoXygQCAZ5RnAvZlno1yhHtnUex_fp7AZ_9nRaO7HX_-SFfGQeutao2TDjDAWU4Vupk8rw9JR0AzZ0N2fvuIAmr_WCsmGpeNqQnev1T7IyEsnh8UMt-n5CafhkikzhEsrmndH6LxOrvRJlsPp6Zv8bUq0k',
            'q'   => 'uKE2dh-cTf6ERF4k4e_jy78GfPYUIaUyoSSJuBzp3Cubk3OCqs6grT8bR_cu0Dm1MZwWmtdqDyI95HrUeq3MP15vMMON8lHTeZu2lmKvwqW7anV5UzhM1iZ7z4yMkuUwFWoBvyY898EXvRD-hdqRxHlSqAZ192zB3pVFJ0s7pFc',
            'dp'  => 'B8PVvXkvJrj2L-GYQ7v3y9r6Kw5g9SahXBwsWUzp19TVlgI-YV85q1NIb1rxQtD-IsXXR3-TanevuRPRt5OBOdiMGQp8pbt26gljYfKU_E9xn-RULHz0-ed9E9gXLKD4VGngpz-PfQ_q29pk5xWHoJp009Qf1HvChixRX59ehik',
            'dq'  => 'CLDmDGduhylc9o7r84rEUVn7pzQ6PF83Y-iBZx5NT-TpnOZKF1pErAMVeKzFEl41DlHHqqBLSM0W1sOFbwTxYWZDm6sI6og5iTbwQGIC3gnJKbi_7k_vJgGHwHxgPaX2PnvP-zyEkDERuf-ry4c_Z11Cq9AqC2yeL6kdKT1cYF8',
            'qi'  => '3PiqvXQN0zwMeE-sBvZgi289XP9XCQF3VWqPzMKnIgQp7_Tugo6-NZBKCQsMf3HaEGBjTVJs_jcK8-TRXvaKe-7ZMaQj8VfBdYkssbu0NKDDhjJ-GtiseaDVWt7dcH0cfwxgFUHpQh7FoCrjFJ6h6ZEpMF6xmujs4qMpPz8aaI4',
        ]);

        $serializeJwt = Build::jws()
            ->exp(time() + 31536000)
            ->iat(time())
            ->nbf(time())
            ->jti('0123456789', true)
            ->alg('RS256')
            ->typ('JWT')
            ->iss(S::TENANT_IDENTIFIER)
            ->aud(S::CLIENT_IDENTIFIER)
            ->sub('5193cabd4a9fbfae66b4238a4842d20da027353f')
            ->claim('tid', S::TENANT_IDENTIFIER)
            ->header('kid', 'something')
            ->sign($jwk);

        return [$jwk, $serializeJwt];
    }
}
