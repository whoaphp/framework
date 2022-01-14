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

namespace Whoa\Validation\Rules\Generic;

use Whoa\Validation\Blocks\IfBlock;
use Whoa\Validation\Contracts\Blocks\ExecutionBlockInterface;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\Rules\BaseRule;

/**
 * @package Whoa\Validation
 */
final class IfOperator extends BaseRule
{
    /**
     * @var callable
     */
    private $condition;

    /**
     * @var RuleInterface|null
     */
    private $onTrue;

    /**
     * @var RuleInterface|null
     */
    private $onFalse;

    /**
     * @var array
     */
    private $settings;

    /**
     * @param callable           $condition
     * @param RuleInterface|null $onTrue
     * @param RuleInterface|null $onFalse
     * @param array              $settings
     */
    public function __construct(
        callable $condition,
        RuleInterface $onTrue = null,
        RuleInterface $onFalse = null,
        array $settings = []
    )
    {
        $this->onTrue    = $onTrue;
        $this->onFalse   = $onFalse;
        $this->condition = $condition;
        $this->settings  = $settings;
    }

    /**
     * @inheritdoc
     */
    public function toBlock(): ExecutionBlockInterface
    {
        $onTrue  = $this->getOnTrue() === null ? new Success() : $this->getOnTrue();
        $onFalse = $this->getOnFalse() === null ? new Success() : $this->getOnFalse();

        return new IfBlock(
            $this->getCondition(),
            $onTrue->setParent($this)->toBlock(),
            $onFalse->setParent($this)->toBlock(),
            $this->getStandardProperties() + $this->getSettings()
        );
    }

    /**
     * @return RuleInterface|null
     */
    public function getOnTrue()
    {
        return $this->onTrue;
    }

    /**
     * @return RuleInterface|null
     */
    public function getOnFalse()
    {
        return $this->onFalse;
    }

    /**
     * @return callable
     */
    public function getCondition(): callable
    {
        return $this->condition;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
