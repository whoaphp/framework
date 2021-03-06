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

namespace Whoa\Application\Data;

use InvalidArgumentException;
use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Contracts\Data\RelationshipTypes;
use ReflectionClass;
use ReflectionException;
use function array_change_key_case;
use function array_key_exists;
use function array_keys;
use function assert;
use function strtolower;

/**
 * @package Whoa\Application
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ModelSchemaInfo implements ModelSchemaInfoInterface
{
    /**
     * @var array
     */
    private $relationshipTypes = [];

    /**
     * @var array
     */
    private $reversedRelationships = [];

    /**
     * @var array
     */
    private $reversedClasses = [];

    /**
     * @var array
     */
    private $foreignKeys = [];

    /**
     * @var array
     */
    private $belongsToMany = [];

    /**
     * @var array
     */
    private $tableNames = [];

    /**
     * @var array
     */
    private $primaryKeys = [];

    /**
     * @var array
     */
    private $attributeTypes = [];

    /**
     * @var array
     */
    private $attributeLengths = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $rawAttributes = [];

    /**
     * @var array
     */
    private $virtualAttributes = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [
            $this->foreignKeys,
            $this->belongsToMany,
            $this->relationshipTypes,
            $this->reversedRelationships,
            $this->tableNames,
            $this->primaryKeys,
            $this->attributeTypes,
            $this->attributeLengths,
            $this->attributes,
            $this->rawAttributes,
            $this->reversedClasses,
            $this->virtualAttributes,
        ];

        return $result;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data): self
    {
        [
            $this->foreignKeys, $this->belongsToMany, $this->relationshipTypes,
            $this->reversedRelationships, $this->tableNames, $this->primaryKeys,
            $this->attributeTypes, $this->attributeLengths, $this->attributes, $this->rawAttributes,
            $this->reversedClasses,
            $this->virtualAttributes
        ] = $data;

        return $this;
    }

    /** @noinspection PhpTooManyParametersInspection
     * @inheritdoc
     *
     * @throws ReflectionException
     */
    public function registerClass(
        string $class,
        string $tableName,
        string $primaryKey,
        array $attributeTypes,
        array $attributeLengths,
        array $rawAttributes = [],
        array $virtualAttributes = []
    ): ModelSchemaInfoInterface
    {
        if (empty($class) === true) {
            throw new InvalidArgumentException('class');
        }

        if (empty($tableName) === true) {
            throw new InvalidArgumentException('tableName');
        }

        if (empty($primaryKey) === true) {
            throw new InvalidArgumentException('primaryKey');
        }

        assert(
            (new ReflectionClass($class))->getName() === $class,
            "Please check name for class `$class`. It should be case sensitive."
        );

        $this->tableNames[$class]        = $tableName;
        $this->primaryKeys[$class]       = $primaryKey;
        $this->attributeTypes[$class]    = $attributeTypes;
        $this->attributeLengths[$class]  = $attributeLengths;
        $this->attributes[$class]        = array_keys($attributeTypes);
        $this->rawAttributes[$class]     = $rawAttributes;
        $this->virtualAttributes[$class] = $virtualAttributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasClass(string $class): bool
    {
        $result = array_key_exists($class, $this->tableNames);

        // check if not found it cannot be found case insensitive (protection from case insensitive values)
        assert(
            $result === true ||
            in_array(strtolower($class), array_change_key_case($this->tableNames, CASE_LOWER)) === false
        );

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getTable(string $class): string
    {
        assert($this->hasClass($class));

        $result = $this->tableNames[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKey(string $class): string
    {
        assert($this->hasClass($class));

        $result = $this->primaryKeys[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeTypes(string $class): array
    {
        assert($this->hasClass($class));

        $result = $this->attributeTypes[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeType(string $class, string $name): string
    {
        assert(
            $this->hasAttributeType($class, $name),
            "Type is not defined for attribute `$name` in class `$class`."
        );

        $result = $this->attributeTypes[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasAttributeType(string $class, string $name): bool
    {
        assert($this->hasClass($class));

        $result = isset($this->attributeTypes[$class][$name]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLengths(string $class): array
    {
        assert($this->hasClass($class));

        $result = $this->attributeLengths[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasAttributeLength(string $class, string $name): bool
    {
        assert($this->hasClass($class));

        $result = isset($this->attributeLengths[$class][$name]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLength(string $class, string $name): int
    {
        assert(
            $this->hasAttributeLength($class, $name) === true,
            "Length not found for column `$name` in class `$class`."
        );

        $result = $this->attributeLengths[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(string $class): array
    {
        assert($this->hasClass($class));

        $result = $this->attributes[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRawAttributes(string $class): array
    {
        assert($this->hasClass($class));

        $result = $this->rawAttributes[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getVirtualAttributes(string $class): array
    {
        assert($this->hasClass($class));

        $result = $this->virtualAttributes[$class];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasRelationship(string $class, string $name): bool
    {
        $result = isset($this->relationshipTypes[$class][$name]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipType(string $class, string $name): int
    {
        assert(
            $this->hasRelationship($class, $name) === true,
            "Relationship `$name` not found in class `$class`."
        );

        $result = $this->relationshipTypes[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getReverseRelationship(string $class, string $name): array
    {
        $result = $this->reversedRelationships[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getReversePrimaryKey(string $class, string $name): array
    {
        $reverseClass = $this->getReverseModelClass($class, $name);

        $table = $this->getTable($reverseClass);
        $key   = $this->getPrimaryKey($reverseClass);

        return [$key, $table];
    }

    /**
     * @inheritdoc
     */
    public function getReverseForeignKey(string $class, string $name): array
    {
        [$reverseClass, $reverseName] = $this->getReverseRelationship($class, $name);

        $table = $this->getTable($reverseClass);
        // would work only if $name is hasMany relationship
        $key = $this->getForeignKey($reverseClass, $reverseName);

        return [$key, $table];
    }

    /**
     * @inheritdoc
     */
    public function getReverseModelClass(string $class, string $name): string
    {
        $reverseClass = $this->reversedClasses[$class][$name];

        return $reverseClass;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKey(string $class, string $name): string
    {
        $result = $this->foreignKeys[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getBelongsToManyRelationship(string $class, string $name): array
    {
        $result = $this->belongsToMany[$class][$name];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function registerBelongsToOneRelationship(
        string $class,
        string $name,
        string $foreignKey,
        string $reverseClass,
        string $reverseName
    ): ModelSchemaInfoInterface
    {
        $this->registerRelationshipType(RelationshipTypes::BELONGS_TO, $class, $name);
        $this->registerRelationshipType(RelationshipTypes::HAS_MANY, $reverseClass, $reverseName);

        $this->registerReversedRelationship($class, $name, $reverseClass, $reverseName);
        $this->registerReversedRelationship($reverseClass, $reverseName, $class, $name);

        $this->foreignKeys[$class][$name] = $foreignKey;

        return $this;
    }

    /** @noinspection PhpTooManyParametersInspection
     * @inheritdoc
     */
    public function registerBelongsToManyRelationship(
        string $class,
        string $name,
        string $table,
        string $foreignKey,
        string $reverseForeignKey,
        string $reverseClass,
        string $reverseName
    ): ModelSchemaInfoInterface
    {
        $this->registerRelationshipType(RelationshipTypes::BELONGS_TO_MANY, $class, $name);
        $this->registerRelationshipType(RelationshipTypes::BELONGS_TO_MANY, $reverseClass, $reverseName);

        // NOTE:
        // `registerReversedRelationship` relies on duplicate registration check in `registerRelationshipType`
        // so it must be called afterwards
        $this->registerReversedRelationship($class, $name, $reverseClass, $reverseName);
        $this->registerReversedRelationship($reverseClass, $reverseName, $class, $name);

        $this->belongsToMany[$class][$name]               = [$table, $foreignKey, $reverseForeignKey];
        $this->belongsToMany[$reverseClass][$reverseName] = [$table, $reverseForeignKey, $foreignKey];

        return $this;
    }

    /**
     * @param int    $type
     * @param string $class
     * @param string $name
     *
     * @return void
     */
    private function registerRelationshipType(int $type, string $class, string $name): void
    {
        assert(empty($class) === false && empty($name) === false);
        assert(
            isset($this->relationshipTypes[$class][$name]) === false,
            "Relationship `$name` for class `$class` was already used."
        );

        $this->relationshipTypes[$class][$name] = $type;
    }

    /**
     * @param string $class
     * @param string $name
     * @param string $reverseClass
     * @param string $reverseName
     *
     * @return void
     */
    private function registerReversedRelationship(
        string $class,
        string $name,
        string $reverseClass,
        string $reverseName
    ): void
    {
        assert(
            empty($class) === false &&
            empty($name) === false &&
            empty($reverseClass) === false &&
            empty($reverseName) === false
        );

        // NOTE:
        // this function relies it would be called after
        // `registerRelationshipType` which prevents duplicate registrations

        $this->reversedRelationships[$class][$name] = [$reverseClass, $reverseName];
        $this->reversedClasses[$class][$name]       = $reverseClass;
    }
}
