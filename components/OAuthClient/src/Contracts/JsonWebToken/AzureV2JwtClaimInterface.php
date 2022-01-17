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

namespace Whoa\OAuthClient\Contracts\JsonWebToken;

/**
 * @package Whoa\OAuthClient
 */
interface AzureV2JwtClaimInterface
{
    /** @var string Key name */
    const KEY_USER_IDENTIFIER = 'oid';

    /** @var string Key name */
    const KEY_ISSUED_AT = 'iat';

    /** @var string Key name */
    const KEY_NOT_BEFORE = 'nbf';

    /** @var string Key name */
    const KEY_EXPIRATION_TIME = 'exp';

    /** @var string Key name */
    const KEY_AUDIENCE = 'aud';

    /** @var string Key name */
    const KEY_TENANT_IDENTIFIER = 'tid';

    /** @var string Key name */
    const KEY_USERNAME = 'preferred_username';
}
