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
interface AzureV2JwtIdentityInterface
{
    /** @var string Key name */
    const KEY_PROVIDER_IDENTIFIER = 'provider_identifier';

    /** @var string Key name */
    const KEY_PROVIDER_NAME = 'provider_name';

    /** @var string Key name */
    const KEY_TENANT_IDENTIFIER = 'tenant_identifier';

    /** @var string Key name */
    const KEY_CLIENT_IDENTIFIER = 'client_identifier';

    /** @var string Key name */
    const KEY_USER_IDENTIFIER = 'user_identifier';

    /** @var string Key name */
    const KEY_USERNAME = 'username';
}
