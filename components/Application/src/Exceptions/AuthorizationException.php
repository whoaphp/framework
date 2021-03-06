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

namespace Whoa\Application\Exceptions;

use Whoa\Contracts\Exceptions\AuthorizationExceptionInterface;
use RuntimeException;
use function assert;
use function is_int;
use function is_string;

/**
 * @package Whoa\Application
 */
class AuthorizationException extends RuntimeException implements AuthorizationExceptionInterface
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var string|null
     */
    private $resourceType;

    /**
     * @var string|int|null
     */
    private $resourceIdentity;

    /**
     * @var array
     */
    private $extraParameters;

    /**
     * @param string $action
     * @param null|string $resourceType
     * @param int|null|string $resourceIdentity
     * @param array $extraParams
     */
    public function __construct(
        string $action,
        string $resourceType = null,
               $resourceIdentity = null,
        array  $extraParams = []
    )
    {
        assert($resourceIdentity === null || is_string($resourceIdentity) || is_int($resourceIdentity));

        $this->action = $action;
        $this->resourceType = $resourceType;
        $this->resourceIdentity = $resourceIdentity;
        $this->extraParameters = $extraParams;
    }

    /**
     * @inheritdoc
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    public function getResourceIdentity()
    {
        return $this->resourceIdentity;
    }

    /**
     * @inheritdoc
     */
    public function getExtraParameters(): array
    {
        return $this->extraParameters;
    }
}
