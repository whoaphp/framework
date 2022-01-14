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

namespace Whoa\Validation;

use Whoa\Validation\Contracts\Execution\ContextStorageInterface;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\Contracts\ValidatorInterface;
use Whoa\Validation\Execution\ContextStorage;
use Whoa\Validation\Validator\BaseValidator;
use Whoa\Validation\Validator\SingleValidation;
use Psr\Container\ContainerInterface;

/**
 * @package Whoa\Validation
 */
class SingleValidator extends BaseValidator
{
    use SingleValidation;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param RuleInterface           $rule
     * @param ContainerInterface|null $container
     */
    public function __construct(RuleInterface $rule, ContainerInterface $container = null)
    {
        parent::__construct();

        $this->setRule($rule);

        $this->container = $container;
    }

    /**
     * @param RuleInterface           $rule
     * @param ContainerInterface|null $container
     *
     * @return ValidatorInterface
     */
    public static function validator(RuleInterface $rule, ContainerInterface $container = null): ValidatorInterface
    {
        $validator = new static($rule, $container);

        return $validator;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function validate($input): bool
    {
        if ($this->areAggregatorsDirty() === true) {
            $this->resetAggregators();
        }

        $this->validateSingleImplementation($input, $this->getCaptureAggregator(), $this->getErrorAggregator());
        $this->markAggregatorsAsDirty();

        $noErrors = $this->getErrorAggregator()->count() <= 0;

        return $noErrors;
    }

    /**
     * @return ContainerInterface|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * During validation you can pass to rules your custom context which might have any additional
     * resources needed by your rules (extra properties, database connection settings, container, and etc).
     *
     * @param array $blocks
     *
     * @return ContextStorageInterface
     */
    protected function createContextStorageFromBlocks(array $blocks): ContextStorageInterface
    {
        return new ContextStorage($blocks, $this->getContainer());
    }
}
