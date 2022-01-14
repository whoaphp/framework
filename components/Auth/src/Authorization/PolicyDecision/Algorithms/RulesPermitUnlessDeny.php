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

namespace Whoa\Auth\Authorization\PolicyDecision\Algorithms;

use Whoa\Auth\Contracts\Authorization\PolicyInformation\ContextInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Whoa\Auth
 */
class RulesPermitUnlessDeny extends BaseRuleAlgorithm
{
    use DefaultTargetSerializeTrait;

    /** @inheritdoc */
    const METHOD = [self::class, 'evaluate'];

    /**
     * @param ContextInterface     $context
     * @param array                $optimizedTargets
     * @param array                $encodedRules
     * @param LoggerInterface|null $logger
     *
     * @return array
     */
    public static function evaluate(
        ContextInterface $context,
        array $optimizedTargets,
        array $encodedRules,
        ?LoggerInterface $logger
    ): array
    {
        return self::evaluatePermitUnlessDeny($context, $optimizedTargets, $encodedRules, $logger);
    }
}
