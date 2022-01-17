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

namespace Whoa\Tests\OAuthClient\Settings;

/**
 * @package Whoa\Tests\OAuthClient
 */
interface AzureV2
{
    /** @var string */
    const PROVIDER_IDENTIFIER = 'generic_provider';

    /** @var string */
    const PROVIDER_NAME = 'generic provider';

    /** @var string */
    const CLIENT_IDENTIFIER = '03f6e001-8322-4e47-aca1-5a8835ae132b';

    /** @var string */
    const TENANT_IDENTIFIER = '6a15c203-0ee9-4136-bac2-838cc929709b';

    /** @var string */
    const VALID_DISCOVERY_DOCUMENT_URI = 'http://127.0.0.1/discovery-document-uri';

    /** @var string */
    const INVALID_DISCOVERY_DOCUMENT_URI = 'http://example/discovery-document-uri';

    /** @var string */
    const JSON_WEB_KEY_SET_ARRAY_KEY = 'jwks_uri';

    /** @var string */
    const VALID_JSON_WEB_KEY_SET_URI = 'http://127.0.0.1/json-web-key-set-uri';

    /** @var string */
    const INVALID_JSON_WEB_KEY_SET_URI = 'http://example/json-web-key-set-uri';

    /** @var string */
    const INVALID_JSON_WEB_KEY_URI = 'http://127.0.0.1/json-web-key-uri';

    /** @var string */
    const VALID_JSON_WEB_KEY_URI = 'http://example/json-web-key-uri';
}
