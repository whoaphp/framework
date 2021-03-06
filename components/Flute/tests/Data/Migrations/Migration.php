<?php

/**
 * Copyright 2015-2019 info@neomerx.com
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

declare (strict_types=1);

namespace Limoncello\Tests\Flute\Data\Migrations;

use Closure;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Limoncello\Contracts\Application\ModelInterface;
use Limoncello\Contracts\Data\RelationshipTypes;
use Limoncello\Tests\Flute\Data\Models\Model;
use Limoncello\Tests\Flute\Data\Types\SystemUuidType;

/**
 * @package Limoncello\Tests\Flute
 */
abstract class Migration
{
    /** Model class */
    const MODEL_CLASS = null;

    /**
     * @return void
     */
    abstract public function migrate();

    /**
     * @var AbstractSchemaManager
     */
    private $schemaManager;

    /**
     * @param AbstractSchemaManager $schemaManager
     */
    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        $tableName = $this->getTableName();
        if ($this->getSchemaManager()->tablesExist([$tableName]) === true) {
            $this->getSchemaManager()->dropTable($tableName);
        }
    }

    /**
     * @return AbstractSchemaManager
     */
    protected function getSchemaManager()
    {
        return $this->schemaManager;
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        $modelClass = $this->getModelClass();

        return $this->getTableNameForClass($modelClass);
    }

    /**
     * @param string    $name
     * @param Closure[] $expressions
     *
     * @return Table
     *
     * @throws DBALException
     */
    protected function createTable(string $name, array $expressions = [])
    {
        $table = new Table($name);

        foreach ($expressions as $expression) {
            /** @var Closure $expression */
            $expression($table);
        }

        $this->getSchemaManager()->dropAndCreateTable($table);

        return $table;
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function primaryInt(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::INTEGER)->setAutoincrement(true)->setUnsigned(true)->setNotnull(true);
            $table->setPrimaryKey([$name]);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function primaryString(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::STRING)->setNotnull(true);
            $table->setPrimaryKey([$name]);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function string(string $name)
    {
        return function (Table $table) use ($name) {
            $modelClass = $this->getModelClass();
            /** @var ModelInterface $modelClass */
            $lengths   = $modelClass::getAttributeLengths();
            $hasLength = array_key_exists($name, $lengths);
            assert($hasLength === true, "String length is not specified for column '$name' in model '$modelClass'.");
            $hasLength ?: null;
            $length = $lengths[$name];
            $table->addColumn($name, Types::STRING, ['length' => $length])->setNotnull(true);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function text(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::TEXT)->setNotnull(true);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function nullableInt(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::INTEGER)->setNotnull(false);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function nullableFloat(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::FLOAT)->setNotnull(false);
        };
    }

    /**
     * @param string $name
     * @param bool   $default
     *
     * @return Closure
     */
    protected function bool(string $name, bool $default = false)
    {
        return function (Table $table) use ($name, $default) {
            $table->addColumn($name, Types::BOOLEAN)->setNotnull(true)->setDefault($default);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function datetime(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::DATETIME_IMMUTABLE)->setNotnull(true);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function nullableDatetime(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, Types::DATETIME_IMMUTABLE)->setNotnull(false);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function uuid(string $name)
    {
        return function (Table $table) use ($name) {
            $table->addColumn($name, SystemUuidType::NAME)->setNotnull(true);
        };
    }

    /**
     * @param string[] $names
     *
     * @return Closure
     */
    protected function unique(array $names)
    {
        return function (Table $table) use ($names) {
            $table->addUniqueIndex($names);
        };
    }

    /**
     * @param string $name
     * @param string $referredClass
     * @param bool   $notNull
     *
     * @return Closure
     */
    protected function foreignInt(string $name, $referredClass, $notNull = true)
    {
        return function (Table $table) use ($name, $referredClass, $notNull) {
            $table->addColumn($name, Types::INTEGER)->setUnsigned(true)->setNotnull($notNull);
            $tableName = $this->getTableNameForClass($referredClass);
            /** @var Model $referredClass */
            assert($tableName !== null, "Table name is not specified for model '$referredClass'.");
            $pkName = $referredClass::FIELD_ID;
            $table->addForeignKeyConstraint($tableName, [$name], [$pkName]);
        };
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function relationship(string $name)
    {
        /** @var ModelInterface $modelClass */
        $modelClass    = $this->getModelClass();
        $relationships = $modelClass::getRelationships();
        $relFound      = isset($relationships[RelationshipTypes::BELONGS_TO][$name]);
        if ($relFound === false) {
            assert($relFound === true, "Belongs-to relationship '$name' not found.");
        }
        assert($relFound === true, "Belongs-to relationship '$name' not found.");
        [$referencedClass, $foreignKey] = $relationships[RelationshipTypes::BELONGS_TO][$name];
        return $this->foreignInt($foreignKey, $referencedClass);
    }

    /**
     * @param string $name
     *
     * @return Closure
     */
    protected function nullableRelationship(string $name)
    {
        /** @var ModelInterface $modelClass */
        $modelClass    = $this->getModelClass();
        $relationships = $modelClass::getRelationships();
        $relFound      = isset($relationships[RelationshipTypes::BELONGS_TO][$name]);
        if ($relFound === false) {
            assert($relFound === true, "Belongs-to relationship '$name' not found.");
        }
        [$referencedClass, $foreignKey] = $relationships[RelationshipTypes::BELONGS_TO][$name];
        return $this->foreignInt($foreignKey, $referencedClass, false);
    }

    /**
     * @param string $modelClass
     *
     * @return string
     */
    protected function getTableNameForClass(string $modelClass)
    {
        /** @var Model $modelClass */
        $tableName = $modelClass::TABLE_NAME;
        assert($tableName !== null, "Table name is not specified for model '$modelClass'.");

        return $tableName;
    }

    /**
     * @return string
     */
    private function getModelClass()
    {
        $modelClass = static::MODEL_CLASS;
        assert($modelClass !== null, 'Model class should be set in migration');

        return $modelClass;
    }
}
