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

namespace Whoa\Validation\Errors;

use Whoa\Validation\Contracts\Errors\ErrorAggregatorInterface;
use Whoa\Validation\Contracts\Errors\ErrorInterface;
use function count;

/**
 * @package Whoa\Validation
 */
class ErrorAggregator implements ErrorAggregatorInterface
{
    /**
     * @var ErrorInterface[]
     */
    private $errors;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function add(ErrorInterface $error): ErrorAggregatorInterface
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function get(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->get());
    }

    /**
     * @inheritdoc
     */
    public function clear(): ErrorAggregatorInterface
    {
        $this->errors = [];

        return $this;
    }
}
