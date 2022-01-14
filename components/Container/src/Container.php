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

namespace Whoa\Container;

use InvalidArgumentException;
use Whoa\Container\Exceptions\NotFoundException;
use Whoa\Contracts\Container\ContainerInterface;

/**
 * @package Whoa\Container
 */
class Container extends \Pimple\Container implements ContainerInterface
{
    /**
     * @inheritdoc
     */
    public function get($identity)
    {
        try {
            return $this->offsetGet($identity);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function has($identity)
    {
        return $this->offsetExists($identity);
    }
}
