<?php

/*
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

namespace Whoa\Application\Packages\Monolog;

use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Settings\Packages\MonologFileSettingsInterface;
use Monolog\Logger;
use function assert;
use function glob;

/**
 * @package Whoa\Application
 */
class MonologFileSettings implements MonologFileSettingsInterface
{
    /**
     * @var array
     */
    private $appConfig;

    /**
     * @inheritdoc
     */
    final public function get(array $appConfig): array
    {
        $this->appConfig = $appConfig;

        $defaults = $this->getSettings();

        $logFolder = $defaults[static::KEY_LOG_FOLDER] ?? null;
        $logFile = $defaults[static::KEY_LOG_FILE] ?? null;
        assert(
            $logFolder !== null && empty(glob($logFolder)) === false,
            "Invalid Logs folder `$logFolder`."
        );
        assert(empty($logFile) === false, "Invalid Logs file name `$logFile`.");

        $logPath = $logFolder . DIRECTORY_SEPARATOR . $logFile;

        return $defaults + [static::KEY_LOG_PATH => $logPath];
    }

    /**
     * @return array
     */
    protected function getSettings(): array
    {
        $appConfig = $this->getAppConfig();

        $isDebug = (bool)($appConfig[A::KEY_IS_DEBUG] ?? false);

        return [
            static::KEY_IS_ENABLED => (bool)($appConfig[A::KEY_IS_LOG_ENABLED] ?? false),
            static::KEY_LOG_LEVEL => $isDebug === true ? Logger::DEBUG : Logger::INFO,
            static::KEY_LOG_FILE => 'limoncello.log',
        ];
    }

    /**
     * @return mixed
     */
    protected function getAppConfig()
    {
        return $this->appConfig;
    }
}
