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

namespace Whoa\Application\Session;

use Iterator;
use Whoa\Application\Contracts\Session\SessionFunctionsInterface;
use Whoa\Contracts\Session\SessionInterface;
use function assert;
use function call_user_func;
use function is_bool;
use function is_int;
use function is_string;

/**
 * @package Whoa\Application
 */
class Session implements SessionInterface
{
    /**
     * @var SessionFunctionsInterface
     */
    private $functions;

    /**
     * @param SessionFunctionsInterface $functions
     */
    public function __construct(SessionFunctionsInterface $functions)
    {
        $this->functions = $functions;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $iterator = call_user_func($this->getFunctions()->getIteratorCallable());

        assert($iterator instanceof Iterator);

        return $iterator;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($key)
    {
        assert(is_string($key) || is_int($key));

        $exists = call_user_func($this->getFunctions()->getHasCallable(), $key);

        assert(is_bool($exists));

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($key)
    {
        assert(is_string($key) || is_int($key));

        $value = call_user_func($this->getFunctions()->getRetrieveCallable(), $key);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($key, $value)
    {
        assert(is_string($key) || is_int($key));

        call_user_func($this->getFunctions()->getPutCallable(), $key, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($key)
    {
        assert(is_string($key) || is_int($key));

        call_user_func($this->getFunctions()->getDeleteCallable(), $key);
    }

    /**
     * @return SessionFunctionsInterface
     */
    protected function getFunctions(): SessionFunctionsInterface
    {
        return $this->functions;
    }
}
