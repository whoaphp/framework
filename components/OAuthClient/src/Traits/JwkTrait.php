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

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Whoa\OAuthClient\Exceptions\RuntimeException;

/**
 * @package Whoa\OAuthClient
 */
trait JwkTrait
{
    /**
     * @param $keys
     *
     * @return JWKSet
     */
    protected function parseJwkSet($keys): JWKSet
    {
        try {
            if ($keys instanceof JWKSet) {
                return $keys;
            } else if ($keys instanceof JWK) {
                return new JWKSet([$keys]);
            } else if (is_array($keys) === true) {
                return JWKSet::createFromKeyData($$keys);
            } else if (is_string($keys) === true) {
                return JWKSet::createFromJson($keys);
            } else {
                throw new RuntimeException(RuntimeException::ERROR_PARSE_JWK_SET);
            }
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_PARSE_JWK_SET, null, 400, [], null, $exception);
        }
    }

    /**
     * @param $values
     *
     * @return JWKSet
     */
    protected function parseJwk($values): JWKSet
    {
        try {
            if ($values instanceof JWKSet) {
                return $this->parseJwkSet($values);
            } else if ($values instanceof JWK) {
                return $this->parseJwkSet(new JWKSet([$values]));
            } else if (is_array($values) === true) {
                return $this->parseJwkSet(new JWKSet([new JWK($values)]));
            } else if (is_string($values) === true) {
                return $this->parseJwkSet(new JWKSet([JWK::createFromJson($values)]));
            } else {
                throw new RuntimeException(RuntimeException::ERROR_PARSE_JWK);
            }
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(RuntimeException::ERROR_PARSE_JWK, null, 400, [], null, $exception);
        }
    }
}
