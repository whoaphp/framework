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

namespace Whoa\Application\Commands;

use Whoa\Common\Reflection\ClassIsTrait;
use Whoa\Contracts\Commands\CommandInterface;
use Whoa\Contracts\Commands\CommandStorageInterface;
use function array_key_exists;
use function assert;

/**
 * @package Whoa\Application
 */
class CommandStorage implements CommandStorageInterface
{
    use ClassIsTrait;

    /**
     * @var string[]
     */
    private $commandClasses = [];

    /**
     * @inheritdoc
     */
    public function has(string $class): bool
    {
        assert($this->isCommand($class));

        return array_key_exists($class, $this->commandClasses);
    }

    /**
     * @inheritdoc
     */
    public function add(string $class): CommandStorageInterface
    {
        assert($this->isCommand($class));

        $this->commandClasses[$class] = $class;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->commandClasses;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isCommand(string $class): bool
    {
        return $this->classImplements($class, CommandInterface::class);
    }
}
