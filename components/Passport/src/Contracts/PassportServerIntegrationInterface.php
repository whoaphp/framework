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

namespace Whoa\Passport\Contracts;

use Whoa\OAuthServer\Contracts\ClientInterface;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Whoa\Passport
 */
interface PassportServerIntegrationInterface
{
    /**
     * @return string
     */
    public function getDefaultClientIdentifier(): string;

    /**
     * @return ClientRepositoryInterface
     */
    public function getClientRepository(): ClientRepositoryInterface;

    /**
     * @return TokenRepositoryInterface
     */
    public function getTokenRepository(): TokenRepositoryInterface;

    /**
     * @return ScopeRepositoryInterface
     */
    public function getScopeRepository(): ScopeRepositoryInterface;

    /**
     * @return RedirectUriRepositoryInterface
     */
    public function getRedirectUriRepository(): RedirectUriRepositoryInterface;

    /**
     * @param string      $userName
     * @param string|null $password
     * @param mixed|null  $extras
     *
     * @return mixed
     */
    public function validateUserId(string $userName, ?string $password = null, $extras = null);

    /**
     * @param int        $userIdentity
     * @param array|null $scope
     *
     * @return array|null
     */
    public function verifyAllowedUserScope(int $userIdentity, array $scope = null): ?array;

    /**
     * @param TokenInterface $token
     *
     * @return string
     */
    public function generateCodeValue(TokenInterface $token): string;

    /**
     * @param TokenInterface $token
     *
     * @return array [string $tokenValue, string $tokenType, int $tokenExpiresInSeconds, string|null $refreshValue]
     */
    public function generateTokenValues(TokenInterface $token): array;

    /**
     * @return int
     */
    public function getCodeExpirationPeriod(): int;

    /**
     * @return int
     */
    public function getTokenExpirationPeriod(): int;

    /**
     * @return ResponseInterface
     */
    public function createInvalidClientAndRedirectUriErrorResponse(): ResponseInterface;

    /** @noinspection PhpTooManyParametersInspection
     * @param string          $type
     * @param ClientInterface $client
     * @param string|null     $redirectUri
     * @param bool            $isScopeModified
     * @param string[]|null   $scopeList
     * @param string|null     $state
     * @param array           $extraParameters
     *
     * @return ResponseInterface
     *
     * @link https://tools.ietf.org/html/rfc6749#section-4.1.2
     * @link https://tools.ietf.org/html/rfc6749#section-4.2.2
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function createAskResourceOwnerForApprovalResponse(
        string $type,
        ClientInterface $client,
        string $redirectUri = null,
        bool $isScopeModified = false,
        array $scopeList = null,
        string $state = null,
        array $extraParameters = []
    ): ResponseInterface;

    /**
     * If token refresh value should be re-newed on token value re-new.
     *
     * @return bool
     */
    public function isRenewRefreshValue(): bool;

    /**
     * @return TokenInterface
     */
    public function createTokenInstance(): TokenInterface;

    /**
     * @param ClientInterface $client
     * @param string          $credentials
     *
     * @return bool
     */
    public function verifyClientCredentials(ClientInterface $client, string $credentials): bool;

    /**
     * This method will be called before token is sent back to client. Developers can add custom
     * properties to the response by returning them from this method.
     *
     * @param TokenInterface $token
     *
     * @return array
     */
    public function getBodyTokenExtraParameters(TokenInterface $token): array;
}
