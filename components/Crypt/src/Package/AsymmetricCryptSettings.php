<?php

/**
 * Copyright 2015-2019 info@neomerx.com
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

namespace Whoa\Crypt\Package;

use Whoa\Contracts\Settings\Packages\AsymmetricCryptSettingsInterface;
use function assert;

/**
 * @package Whoa\Crypt
 */
class AsymmetricCryptSettings implements AsymmetricCryptSettingsInterface
{
    /**
     * @inheritdoc
     */
    final public function get(array $appConfig): array
    {
        $defaults = $this->getSettings();

        $publicValue = $defaults[static::KEY_PUBLIC_PATH_OR_KEY_VALUE];
        assert(empty($publicValue) === false, "Public key/value cannot be empty.");

        $privateValue = $defaults[static::KEY_PUBLIC_PATH_OR_KEY_VALUE];
        assert(empty($privateValue) === false, "Private key/value cannot be empty.");

        return $defaults;
    }

    /**
     * @return array
     */
    protected function getSettings(): array
    {
        return [];
    }
}
