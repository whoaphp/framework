<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
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

namespace Whoa\Passport\Contracts\Repositories;

use Closure;
use Whoa\Passport\Contracts\Entities\ClientInterface;
use Whoa\Passport\Contracts\Entities\ScopeInterface;

/**
 * @package Whoa\Passport
 */
interface ClientRepositoryInterface
{
    /**
     * @param Closure $closure
     *
     * @return void
     */
    public function inTransaction(Closure $closure): void;

    /**
     * @return ClientInterface[]
     */
    public function index(): array;

    /**
     * @param ClientInterface $client
     *
     * @return ClientInterface
     */
    public function create(ClientInterface $client): ClientInterface;

    /**
     * @param string                    $identifier
     * @param iterable|ScopeInterface[] $scopes
     *
     * @return void
     */
    public function bindScopes(string $identifier, iterable $scopes): void;

    /**
     * @param string            $identifier
     * @param iterable|string[] $scopeIdentifiers
     *
     * @return void
     */
    public function bindScopeIdentifiers(string $identifier, iterable $scopeIdentifiers): void;

    /**
     * @param string $identifier
     *
     * @return void
     */
    public function unbindScopes(string $identifier): void;

    /**
     * @param string $identifier
     *
     * @return ClientInterface|null
     */
    public function read(string $identifier): ?ClientInterface;

    /**
     * @param string $identifier
     *
     * @return string[]
     */
    public function readScopeIdentifiers(string $identifier): array;

    /**
     * @param string $identifier
     *
     * @return string[]
     */
    public function readRedirectUriStrings(string $identifier): array;

    /**
     * @param ClientInterface $client
     *
     * @return void
     */
    public function update(ClientInterface $client): void;

    /**
     * @param string $identifier
     *
     * @return void
     */
    public function delete(string $identifier): void;
}
