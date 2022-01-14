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

use Whoa\Validation\Blocks\AndBlock;
use Whoa\Validation\Blocks\ProcedureBlock;
use Whoa\Validation\Contracts\Blocks\ExecutionBlockInterface;
use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\Execution\BlockReplies;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\BaseRule;

/**
 * @package Whoa\Validation
 */
final class Required extends BaseRule
{
    /**
     * State key.
     */
    private const STATE_HAS_BEEN_CALLED = self::STATE_LAST + 1;

    /**
     * @var RuleInterface
     */
    private $rule;

    /**
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @inheritdoc
     */
    public function toBlock(): ExecutionBlockInterface
    {
        $calledCheck = (new ProcedureBlock([self::class, 'execute']))
            ->setProperties($this->composeStandardProperties($this->getName(), false))
            ->setEndCallable([self::class, 'end']);
        $required    = new AndBlock(
            $calledCheck,
            $this->getRule()->setParent($this)->toBlock(),
            $this->getStandardProperties()
        );

        return $required;
    }

    /**
     * @param mixed            $value
     * @param ContextInterface $context
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function execute($value, ContextInterface $context): array
    {
        $context->getStates()->setState(static::STATE_HAS_BEEN_CALLED, true);

        return static::createSuccessReply($value);
    }

    /**
     * @param ContextInterface $context
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function end(ContextInterface $context): array
    {
        $isOk = $context->getStates()->getState(static::STATE_HAS_BEEN_CALLED, false);

        return $isOk === true ? BlockReplies::createEndSuccessReply() :
            BlockReplies::createEndErrorReply($context, ErrorCodes::REQUIRED, Messages::REQUIRED, []);
    }

    /**
     * @return RuleInterface
     */
    public function getRule(): RuleInterface
    {
        return $this->rule;
    }
}
