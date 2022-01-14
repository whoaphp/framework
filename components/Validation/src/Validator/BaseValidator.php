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

namespace Whoa\Validation\Validator;

use Whoa\Validation\Captures\CaptureAggregator;
use Whoa\Validation\Contracts\Captures\CaptureAggregatorInterface;
use Whoa\Validation\Contracts\Errors\ErrorAggregatorInterface;
use Whoa\Validation\Contracts\ValidatorInterface;
use Whoa\Validation\Errors\ErrorAggregator;

/**
 * @package Whoa\Validation
 */
abstract class BaseValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    private $areAggregatorsDirty = false;

    /**
     * @var CaptureAggregatorInterface
     */
    private $captures;

    /**
     * @var ErrorAggregatorInterface
     */
    private $errors;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->resetAggregators();
    }

    /**
     * @inheritdoc
     */
    public function getCaptures(): array
    {
        return $this->getCaptureAggregator()->get();
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->getErrorAggregator()->get();
    }

    /**
     * @return CaptureAggregatorInterface
     */
    protected function getCaptureAggregator(): CaptureAggregatorInterface
    {
        return $this->captures;
    }

    /**
     * @return ErrorAggregatorInterface
     */
    protected function getErrorAggregator(): ErrorAggregatorInterface
    {
        return $this->errors;
    }

    /**
     * @return CaptureAggregatorInterface
     */
    protected function createCaptureAggregator(): CaptureAggregatorInterface
    {
        return new CaptureAggregator();
    }

    /**
     * @return ErrorAggregatorInterface
     */
    protected function createErrorAggregator(): ErrorAggregatorInterface
    {
        return new ErrorAggregator();
    }

    /**
     * @return bool
     */
    protected function areAggregatorsDirty(): bool
    {
        return $this->areAggregatorsDirty;
    }

    /**
     * @return self
     */
    protected function markAggregatorsAsDirty(): self
    {
        $this->areAggregatorsDirty = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function resetAggregators(): self
    {
        $this->captures = $this->createCaptureAggregator();
        $this->errors   = $this->createErrorAggregator();

        $this->areAggregatorsDirty = false;

        return $this;
    }
}
