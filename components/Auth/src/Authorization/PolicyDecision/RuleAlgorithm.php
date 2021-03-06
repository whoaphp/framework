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

namespace Whoa\Auth\Authorization\PolicyDecision;

use Whoa\Auth\Authorization\PolicyDecision\Algorithms\RulesDenyOverrides;
use Whoa\Auth\Authorization\PolicyDecision\Algorithms\RulesDenyUnlessPermit;
use Whoa\Auth\Authorization\PolicyDecision\Algorithms\RulesFirstApplicable;
use Whoa\Auth\Authorization\PolicyDecision\Algorithms\RulesPermitOverrides;
use Whoa\Auth\Authorization\PolicyDecision\Algorithms\RulesPermitUnlessDeny;
use Whoa\Auth\Contracts\Authorization\PolicyAdministration\RuleCombiningAlgorithmInterface;

/**
 * @package Whoa\Auth
 */
abstract class RuleAlgorithm
{
    /**
     * @return RuleCombiningAlgorithmInterface
     */
    public static function firstApplicable(): RuleCombiningAlgorithmInterface
    {
        return new RulesFirstApplicable();
    }

    /**
     * @return RuleCombiningAlgorithmInterface
     */
    public static function denyOverrides(): RuleCombiningAlgorithmInterface
    {
        return new RulesDenyOverrides();
    }

    /**
     * @return RuleCombiningAlgorithmInterface
     */
    public static function permitOverrides(): RuleCombiningAlgorithmInterface
    {
        return new RulesPermitOverrides();
    }

    /**
     * @return RuleCombiningAlgorithmInterface
     */
    public static function denyUnlessPermit(): RuleCombiningAlgorithmInterface
    {
        return new RulesDenyUnlessPermit();
    }

    /**
     * @return RuleCombiningAlgorithmInterface
     */
    public static function permitUnlessDeny(): RuleCombiningAlgorithmInterface
    {
        return new RulesPermitUnlessDeny();
    }
}
