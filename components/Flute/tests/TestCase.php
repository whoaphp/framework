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

namespace Limoncello\Tests\Flute;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Generator;
use Limoncello\Common\Reflection\ClassIsTrait;
use Limoncello\Contracts\Application\ModelInterface;
use Limoncello\Contracts\Data\ModelSchemaInfoInterface;
use Limoncello\Contracts\Data\RelationshipTypes;
use Limoncello\Flute\Contracts\FactoryInterface;
use Limoncello\Flute\Contracts\Schema\JsonSchemasInterface;
use Limoncello\Flute\Contracts\Schema\SchemaInterface;
use Limoncello\Tests\Flute\Data\Migrations\Runner as MigrationRunner;
use Limoncello\Tests\Flute\Data\Models\Board;
use Limoncello\Tests\Flute\Data\Models\Category;
use Limoncello\Tests\Flute\Data\Models\Comment;
use Limoncello\Tests\Flute\Data\Models\Emotion;
use Limoncello\Tests\Flute\Data\Models\ModelSchemas;
use Limoncello\Tests\Flute\Data\Models\Post;
use Limoncello\Tests\Flute\Data\Models\PostExtended;
use Limoncello\Tests\Flute\Data\Models\Role;
use Limoncello\Tests\Flute\Data\Models\StringPKModel;
use Limoncello\Tests\Flute\Data\Models\User;
use Limoncello\Tests\Flute\Data\Schemas\BoardSchema;
use Limoncello\Tests\Flute\Data\Schemas\CategorySchema;
use Limoncello\Tests\Flute\Data\Schemas\CommentSchema;
use Limoncello\Tests\Flute\Data\Schemas\EmotionSchema;
use Limoncello\Tests\Flute\Data\Schemas\PostSchema;
use Limoncello\Tests\Flute\Data\Schemas\RoleSchema;
use Limoncello\Tests\Flute\Data\Schemas\UserSchema;
use Limoncello\Tests\Flute\Data\Seeds\Runner as SeedRunner;
use Limoncello\Tests\Flute\Data\Validation\JsonData\CreateBoardRules;
use Limoncello\Tests\Flute\Data\Validation\JsonData\CreateCommentRules;
use Limoncello\Tests\Flute\Data\Validation\JsonData\UpdateBoardRules;
use Limoncello\Tests\Flute\Data\Validation\JsonData\UpdateCommentRules;
use Limoncello\Tests\Flute\Data\Validation\JsonData\UpdatePostRules;
use Limoncello\Tests\Flute\Data\Validation\JsonData\UpdateUserMinimalRules;
use Mockery;

/**
 * @package Limoncello\Tests\Flute
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use ClassIsTrait;

    /**
     * @param array $modelClasses
     * @param bool  $requireReverseRelationships
     *
     * @return ModelSchemaInfoInterface
     */
    public static function createSchemas(
        array $modelClasses,
        $requireReverseRelationships = true
    ): ModelSchemaInfoInterface
    {
        $registered    = [];
        $modelSchemas  = new ModelSchemas();
        $registerModel = function (string $modelClass) use ($modelSchemas, &$registered, $requireReverseRelationships) {
            /** @var ModelInterface $modelClass */
            $modelSchemas->registerClass(
                (string)$modelClass, $modelClass::getTableName(), $modelClass::getPrimaryKeyName(), $modelClass::getAttributeTypes(), $modelClass::getAttributeLengths(), $modelClass::getRawAttributes()
            );

            $relationships = $modelClass::getRelationships();

            if (array_key_exists(RelationshipTypes::BELONGS_TO, $relationships) === true) {
                foreach ($relationships[RelationshipTypes::BELONGS_TO] as $relName => [$rClass, $fKey, $rRel]) {
                    /** @var string $rClass */
                    $modelSchemas->registerBelongsToOneRelationship($modelClass, $relName, $fKey, $rClass, $rRel);
                    $registered[(string)$modelClass][$relName] = true;
                    $registered[$rClass][$rRel]                = true;

                    // Sanity check. Every `belongs_to` should be paired with `has_many` on the other side.
                    /** @var ModelInterface $rClass */
                    $rRelationships   = $rClass::getRelationships();
                    $isRelationshipOk = $requireReverseRelationships === false ||
                        (isset($rRelationships[RelationshipTypes::HAS_MANY][$rRel]) === true &&
                            $rRelationships[RelationshipTypes::HAS_MANY][$rRel] === [$modelClass, $fKey, $relName]);
                    /** @var string $modelClass */

                    assert($isRelationshipOk, "`belongsTo` relationship `$relName` of class $modelClass " .
                        "should be paired with `hasMany` relationship.");
                }
            }

            if (array_key_exists(RelationshipTypes::HAS_MANY, $relationships) === true) {
                foreach ($relationships[RelationshipTypes::HAS_MANY] as $relName => [$rClass, $fKey, $rRel]) {
                    // Sanity check. Every `has_many` should be paired with `belongs_to` on the other side.
                    /** @var ModelInterface $rClass */
                    $rRelationships   = $rClass::getRelationships();
                    $isRelationshipOk = $requireReverseRelationships === false ||
                        (isset($rRelationships[RelationshipTypes::BELONGS_TO][$rRel]) === true &&
                            $rRelationships[RelationshipTypes::BELONGS_TO][$rRel] === [$modelClass, $fKey, $relName]);
                    /** @var string $modelClass */
                    assert($isRelationshipOk, "`hasMany` relationship `$relName` of class $modelClass " .
                        "should be paired with `belongsTo` relationship.");
                }
            }

            if (array_key_exists(RelationshipTypes::BELONGS_TO_MANY, $relationships) === true) {
                foreach ($relationships[RelationshipTypes::BELONGS_TO_MANY] as $relName => $data) {
                    if (isset($registered[(string)$modelClass][$relName]) === true) {
                        continue;
                    }
                    /** @var string $rClass */
                    [$rClass, $iTable, $fKeyPrimary, $fKeySecondary, $rRel] = $data;
                    $modelSchemas->registerBelongsToManyRelationship(
                        $modelClass,
                        $relName,
                        $iTable,
                        $fKeyPrimary,
                        $fKeySecondary,
                        $rClass,
                        $rRel
                    );
                    $registered[(string)$modelClass][$relName] = true;
                    $registered[$rClass][$rRel]                = true;
                }
            }
        };

        array_map($registerModel, $modelClasses);

        return $modelSchemas;
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @return Connection
     *
     * @throws Exception
     * @throws DBALException
     */
    protected function createConnection()
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///', 'memory' => true]);
        $this->assertNotSame(false, $connection->executeStatement('PRAGMA foreign_keys = ON;'));

        return $connection;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    protected function migrateDatabase(Connection $connection)
    {
        (new MigrationRunner())->migrate($connection->getSchemaManager());
        (new SeedRunner())->run($connection);
    }

    /**
     * @return Connection
     *
     * @throws Exception
     * @throws DBALException
     */
    protected function initDb()
    {
        $connection = $this->createConnection();
        $this->migrateDatabase($connection);

        return $connection;
    }

    /**
     * @return ModelSchemaInfoInterface
     */
    protected function getModelSchemas()
    {
        $modelSchemas = static::createSchemas([
            Board::class,
            Comment::class,
            Emotion::class,
            Post::class,
            Role::class,
            User::class,
            Category::class,
            StringPKModel::class,
            PostExtended::class,
        ]);

        return $modelSchemas;
    }

    /**
     * @param FactoryInterface         $factory
     * @param ModelSchemaInfoInterface $modelSchemas
     *
     * @return JsonSchemasInterface
     */
    protected function getJsonSchemas(FactoryInterface $factory, ModelSchemaInfoInterface $modelSchemas)
    {
        [$modelToSchemaMap, $typeToSchemaMap] = $this->getSchemaMap();

        $schemas = $factory->createJsonSchemas($modelToSchemaMap, $typeToSchemaMap, $modelSchemas);

        return $schemas;
    }

    /**
     * @return array
     */
    protected function getSchemaMap()
    {
        $modelToSchemaMap = [
            Board::class    => BoardSchema::class,
            Comment::class  => CommentSchema::class,
            Emotion::class  => EmotionSchema::class,
            Post::class     => PostSchema::class,
            Role::class     => RoleSchema::class,
            User::class     => UserSchema::class,
            Category::class => CategorySchema::class,
        ];

        $typeToSchemaMap = [];
        foreach ($modelToSchemaMap as $modelClass => $schemaClass) {
            assert(static::classImplements($schemaClass, SchemaInterface::class));
            /** @var SchemaInterface $schemaClass */
            $type                   = $schemaClass::TYPE;
            $typeToSchemaMap[$type] = $schemaClass;
        }

        return [$modelToSchemaMap, $typeToSchemaMap];
    }

    /**
     * @return array
     */
    protected function getJsonValidationRuleSets()
    {
        return [
            CreateBoardRules::class,
            UpdateBoardRules::class,
            CreateCommentRules::class,
            UpdateCommentRules::class,
            UpdatePostRules::class,
            UpdateUserMinimalRules::class,
        ];
    }

    /**
     * @return array
     */
    protected function getFormValidationRuleSets()
    {
        return [
            Data\Validation\Forms\CreateCommentRules::class,
            Data\Validation\Forms\UpdateCommentRules::class,
        ];
    }

    /**
     * @return array
     */
    protected function getQueryValidationRuleSets()
    {
        return [
            Data\Validation\JsonQueries\AllowEverythingRules::class,
            Data\Validation\JsonQueries\CommentsIndexRules::class,
            Data\Validation\JsonQueries\ReadCommentsQueryRules::class,
            Data\Validation\JsonQueries\ReadBoardsQueryRules::class,
            Data\Validation\JsonQueries\ReadCategoriesQueryRules::class,
            Data\Validation\JsonQueries\ReadUsersQueryRules::class,
            Data\Validation\JsonQueries\ReadEmotionsFromCommentsQueryRules::class,
            Data\Validation\JsonQueries\ReadPostsQueryRules::class,
        ];
    }

    /**
     * @param iterable $iterable
     *
     * @return array
     */
    protected function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Generator ? iterator_to_array($iterable) : $iterable;
    }

    /**
     * @param iterable $iterable
     *
     * @return array
     */
    protected function deepIterableToArray(iterable $iterable): array
    {
        $result = [];

        foreach ($iterable as $key => $value) {
            $result[$key] = $value instanceof Generator ? $this->deepIterableToArray($value) : $value;
        }

        return $result;
    }
}
