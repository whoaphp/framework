<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Copyright 2021 info@whoaphp.com
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

namespace Whoa\Tests\Validation\Rules;

use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\Rules\ExecuteRule;
use PDO;
use function assert;

/**
 * @package Sample
 */
class DbRule extends ExecuteRule
{
    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        // it emulates it takes connection from context's container and work with database.
        $pdo = $context->getContainer()->get(PDO::class);
        assert($pdo !== null);

        return static::createSuccessReply($value);
    }
}
