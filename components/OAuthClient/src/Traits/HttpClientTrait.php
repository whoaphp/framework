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

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Whoa\OAuthClient\Exceptions\InvalidArgumentException;

/**
 * @package Whoa\OAuthClient
 */
trait HttpClientTrait
{
    /**
     * @var HttpClient|null
     */
    protected ?HttpClient $httpClient = null;

    /**
     * @param string $uri
     *
     * @return string|null
     * @throws GuzzleException
     */
    protected function getDataFromUri(string $uri): ?string
    {
        if ($this->getHttpClient() === null) {
            throw new InvalidArgumentException(InvalidArgumentException::ERROR_MISSING_HTTP_CLIENT);
        }

        return (string)$this->getHttpClient()
                ->get($uri)
                ->getBody() ?? null;
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient(): HttpClient
    {
        if ($this->httpClient === null) {
            $this->httpClient = new HttpClient();
        }

        return $this->httpClient;
    }
}
