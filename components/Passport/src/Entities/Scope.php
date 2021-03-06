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

namespace Whoa\Passport\Entities;

use DateTimeInterface;
use Whoa\Passport\Contracts\Entities\ScopeInterface;
use Whoa\Passport\Models\Scope as Model;

/**
 * @package Whoa\Passport
 */
abstract class Scope extends DatabaseItem implements ScopeInterface
{
    /** Field name */
    const FIELD_ID = Model::FIELD_ID;

    /** Field name */
    const FIELD_DESCRIPTION = Model::FIELD_DESCRIPTION;

    /**
     * @var string|null
     */
    private $identifierField;

    /**
     * @var string|null
     */
    private $descriptionField;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this
                ->setIdentifier($this->{static::FIELD_ID})
                ->setDescription($this->{static::FIELD_DESCRIPTION});
        }
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifierField;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier): ScopeInterface
    {
        $this->identifierField = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid = null): ScopeInterface
    {
        /** @var ScopeInterface $self */
        $self = $this->setUuidImpl($uuid);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->descriptionField;
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description = null): ScopeInterface
    {
        $this->descriptionField = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(DateTimeInterface $createdAt): ScopeInterface
    {
        /** @var ScopeInterface $self */
        $self = $this->setCreatedAtImpl($createdAt);

        return $self;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(DateTimeInterface $createdAt): ScopeInterface
    {
        /** @var ScopeInterface $self */
        $self = $this->setUpdatedAtImpl($createdAt);

        return $self;
    }
}
