<?php declare (strict_types=1);

namespace Limoncello\Tests\Flute\Http;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Exception;
use Limoncello\Container\Container;
use Limoncello\Flute\Contracts\Http\Query\FilterParameterInterface;
use Limoncello\Flute\Contracts\Http\Query\ParametersMapperInterface;
use Limoncello\Flute\Contracts\Http\Query\RelationshipInterface;
use Limoncello\Flute\Contracts\Schema\JsonSchemasInterface;
use Limoncello\Flute\Contracts\Schema\SchemaInterface;
use Limoncello\Flute\Factory;
use Limoncello\Flute\Http\Query\ParametersMapper;
use Limoncello\Flute\Http\Query\SortParameter;
use Limoncello\Tests\Flute\Data\Models\Board;
use Limoncello\Tests\Flute\Data\Schemas\BoardSchema;
use Limoncello\Tests\Flute\Data\Schemas\CommentSchema;
use Limoncello\Tests\Flute\Data\Schemas\EmotionSchema;
use Limoncello\Tests\Flute\Data\Schemas\PostSchema;
use Limoncello\Tests\Flute\TestCase;

/**
 * @package Limoncello\Tests\Flute
 */
class ParametersMapperTest extends TestCase
{
    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testGetFiltersForVariousFieldTypes(): void
    {
        $filterParameters = [
            BoardSchema::RESOURCE_ID                              => [
                'in' => ['10', '11'],
            ],
            BoardSchema::ATTR_TITLE                               => [
                'like' => ['like%'],
            ],
            BoardSchema::REL_POSTS                                => [
                'eq' => ['1'],
            ],
            BoardSchema::REL_POSTS . '.' . PostSchema::ATTR_TITLE => [
                'not-like' => ['not_like%'],
            ],
        ];

        $mapper = $this->createMapper(BoardSchema::class)->withFilters($filterParameters);

        /** @var FilterParameterInterface[] $filters */
        $filters = $this->deepIterableToArray($mapper->getMappedFilters());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(4, $filters);

        $filter = $filters[0];
        $this->assertNull($filter->getRelationship());
        $this->assertNotNull($filter->getAttribute());
        $this->assertEquals(BoardSchema::RESOURCE_ID, $filter->getAttribute()->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $filter->getAttribute()->getSchema()::TYPE);
        $this->assertEquals([
            FilterParameterInterface::OPERATION_IN => ['10', '11'],
        ], $this->deepIterableToArray($filter->getOperationsWithArguments()));

        $filter = $filters[1];
        $this->assertNull($filter->getRelationship());
        $this->assertNotNull($filter->getAttribute());
        $this->assertEquals(BoardSchema::ATTR_TITLE, $filter->getAttribute()->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $filter->getAttribute()->getSchema()::TYPE);
        $this->assertEquals([
            FilterParameterInterface::OPERATION_LIKE => ['like%'],
        ], $this->deepIterableToArray($filter->getOperationsWithArguments()));

        $filter = $filters[2];
        $this->assertNotNull($filter->getRelationship());
        $this->assertNotNull($filter->getAttribute());
        $this->assertEquals(BoardSchema::TYPE, $filter->getRelationship()->getFromSchema()::TYPE);
        $this->assertEquals(PostSchema::TYPE, $filter->getRelationship()->getToSchema()::TYPE);
        $this->assertEquals(BoardSchema::REL_POSTS, $filter->getRelationship()->getNameInSchema());
        $this->assertEquals(Board::REL_POSTS, $filter->getRelationship()->getNameInModel());
        $this->assertEquals(PostSchema::RESOURCE_ID, $filter->getAttribute()->getNameInSchema());
        $this->assertEquals(PostSchema::TYPE, $filter->getAttribute()->getSchema()::TYPE);
        $this->assertEquals([
            FilterParameterInterface::OPERATION_EQUALS => ['1'],
        ], $this->deepIterableToArray($filter->getOperationsWithArguments()));

        $filter = $filters[3];
        $this->assertNotNull($filter->getRelationship());
        $this->assertNotNull($filter->getAttribute());
        $this->assertEquals(BoardSchema::TYPE, $filter->getRelationship()->getFromSchema()::TYPE);
        $this->assertEquals(PostSchema::TYPE, $filter->getRelationship()->getToSchema()::TYPE);
        $this->assertEquals(BoardSchema::REL_POSTS, $filter->getRelationship()->getNameInSchema());
        $this->assertEquals(Board::REL_POSTS, $filter->getRelationship()->getNameInModel());
        $this->assertEquals(PostSchema::ATTR_TITLE, $filter->getAttribute()->getNameInSchema());
        $this->assertEquals(PostSchema::TYPE, $filter->getAttribute()->getSchema()::TYPE);
        $this->assertEquals([
            FilterParameterInterface::OPERATION_NOT_LIKE => ['not_like%'],
        ], $this->deepIterableToArray($filter->getOperationsWithArguments()));
    }

    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testGetFiltersForVariousOperations(): void
    {
        $filterParameters = [
            BoardSchema::RESOURCE_ID => [
                'equals'            => ['1'],
                'not-equals'        => ['1'],
                'less-than'         => ['1'],
                'less-or-equals'    => ['1'],
                'greater-than'      => ['1'],
                'greater-or-equals' => ['1'],
                'like'              => ['1'],
                'not-like'          => ['1'],
                'in'                => ['1', '2'],
                'not-in'            => ['1', '2'],
                'is-null'           => [],
                'not-null'          => [],
            ],
        ];

        $mapper = $this->createMapper(BoardSchema::class)->withFilters($filterParameters);

        /** @var FilterParameterInterface[] $filters */
        $filters = $this->deepIterableToArray($mapper->getMappedFilters());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $filters);

        $filter = reset($filters);
        $this->assertNull($filter->getRelationship());
        $this->assertNotNull($filter->getAttribute());
        $this->assertEquals(BoardSchema::RESOURCE_ID, $filter->getAttribute()->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $filter->getAttribute()->getSchema()::TYPE);
        $this->assertEquals([
            FilterParameterInterface::OPERATION_EQUALS            => ['1'],
            FilterParameterInterface::OPERATION_NOT_EQUALS        => ['1'],
            FilterParameterInterface::OPERATION_LESS_THAN         => ['1'],
            FilterParameterInterface::OPERATION_LESS_OR_EQUALS    => ['1'],
            FilterParameterInterface::OPERATION_GREATER_THAN      => ['1'],
            FilterParameterInterface::OPERATION_GREATER_OR_EQUALS => ['1'],
            FilterParameterInterface::OPERATION_LIKE              => ['1'],
            FilterParameterInterface::OPERATION_NOT_LIKE          => ['1'],
            FilterParameterInterface::OPERATION_IN                => ['1', '2'],
            FilterParameterInterface::OPERATION_NOT_IN            => ['1', '2'],
            FilterParameterInterface::OPERATION_IS_NULL           => [],
            FilterParameterInterface::OPERATION_IS_NOT_NULL       => [],
        ], $this->deepIterableToArray($filter->getOperationsWithArguments()));
    }

    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testGetFiltersForUnknownOperation(): void
    {
        $this->expectException(\Limoncello\Flute\Exceptions\InvalidQueryParametersException::class);

        $filterParameters = [
            BoardSchema::RESOURCE_ID => [
                'non-existing-operation' => [],
            ],
        ];

        $mapper = $this->createMapper(BoardSchema::class)->withFilters($filterParameters);

        /** @var FilterParameterInterface[] $filters */
        $filters = $this->deepIterableToArray($mapper->getMappedFilters());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $filters);

        $filter = reset($filters);
        $this->deepIterableToArray($filter->getOperationsWithArguments());
    }

    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testGetFiltersForUnknownField(): void
    {
        $this->expectException(\Limoncello\Flute\Exceptions\InvalidQueryParametersException::class);

        $filterParameters = [
            'non_existing_field' => ['equals' => ['1']],
        ];

        $mapper = $this->createMapper(BoardSchema::class)->withFilters($filterParameters);

        /** @var FilterParameterInterface[] $filters */
        $filters = $this->deepIterableToArray($mapper->getMappedFilters());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $filters);

        $filter = reset($filters);
        $this->deepIterableToArray($filter->getOperationsWithArguments());
    }

    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testGetSorts(): void
    {
        $sortParameters = [
            BoardSchema::RESOURCE_ID                              => true,
            BoardSchema::ATTR_TITLE                               => false,
            BoardSchema::REL_POSTS                                => true,
            BoardSchema::REL_POSTS . '.' . PostSchema::ATTR_TITLE => false,
        ];

        $mapper = $this->createMapper(BoardSchema::class)->withSorts($sortParameters);
        /** @var SortParameter[] $sorts */
        $sorts = $this->deepIterableToArray($mapper->getMappedSorts());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(4, $sorts);

        $sort = $sorts[0];
        $this->assertNull($sort->getRelationship());
        $this->assertNotNull($sort->getAttribute());
        $this->assertEquals(BoardSchema::RESOURCE_ID, $sort->getAttribute()->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $sort->getAttribute()->getSchema()::TYPE);
        $this->assertTrue($sort->isAsc());

        $sort = $sorts[1];
        $this->assertNull($sort->getRelationship());
        $this->assertNotNull($sort->getAttribute());
        $this->assertEquals(BoardSchema::ATTR_TITLE, $sort->getAttribute()->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $sort->getAttribute()->getSchema()::TYPE);
        $this->assertTrue($sort->isDesc());

        $sort = $sorts[2];
        $this->assertNotNull($sort->getRelationship());
        $this->assertNotNull($sort->getAttribute());
        $this->assertEquals(BoardSchema::TYPE, $sort->getRelationship()->getFromSchema()::TYPE);
        $this->assertEquals(PostSchema::TYPE, $sort->getRelationship()->getToSchema()::TYPE);
        $this->assertEquals(BoardSchema::REL_POSTS, $sort->getRelationship()->getNameInSchema());
        $this->assertEquals(Board::REL_POSTS, $sort->getRelationship()->getNameInModel());
        $this->assertEquals(PostSchema::RESOURCE_ID, $sort->getAttribute()->getNameInSchema());
        $this->assertEquals(PostSchema::TYPE, $sort->getAttribute()->getSchema()::TYPE);
        $this->assertTrue($sort->isAsc());

        $sort = $sorts[3];
        $this->assertNotNull($sort->getRelationship());
        $this->assertNotNull($sort->getAttribute());
        $this->assertEquals(BoardSchema::TYPE, $sort->getRelationship()->getFromSchema()::TYPE);
        $this->assertEquals(PostSchema::TYPE, $sort->getRelationship()->getToSchema()::TYPE);
        $this->assertEquals(BoardSchema::REL_POSTS, $sort->getRelationship()->getNameInSchema());
        $this->assertEquals(Board::REL_POSTS, $sort->getRelationship()->getNameInModel());
        $this->assertEquals(PostSchema::ATTR_TITLE, $sort->getAttribute()->getNameInSchema());
        $this->assertEquals(PostSchema::TYPE, $sort->getAttribute()->getSchema()::TYPE);
        $this->assertTrue($sort->isDesc());
    }

    /**
     * Test query.
     *
     * @throws Exception
     */
    public function testIncludes(): void
    {
        $path1 = [BoardSchema::REL_POSTS];
        $path2 = [BoardSchema::REL_POSTS, PostSchema::REL_COMMENTS];
        $path3 = [BoardSchema::REL_POSTS, PostSchema::REL_COMMENTS, CommentSchema::REL_EMOTIONS];

        $includeParameters = [$path1, $path2, $path3];

        $mapper = $this->createMapper(BoardSchema::class)->withIncludes($includeParameters);

        $includes = $this->deepIterableToArray($mapper->getMappedIncludes());
        $this->assertCount(3, $includes);

        /** @var RelationshipInterface[] $include */
        $include = $includes[0];
        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $include);
        $this->assertEquals(BoardSchema::REL_POSTS, $include[0]->getNameInSchema());
        $this->assertEquals(BoardSchema::TYPE, $include[0]->getFromSchema()::TYPE);
        $this->assertEquals(PostSchema::TYPE, $include[0]->getToSchema()::TYPE);

        $include = $includes[1];
        $this->assertEquals(2, count($include));
        $this->assertEquals(PostSchema::REL_COMMENTS, $include[1]->getNameInSchema());
        $this->assertEquals(PostSchema::TYPE, $include[1]->getFromSchema()::TYPE);
        $this->assertEquals(CommentSchema::TYPE, $include[1]->getToSchema()::TYPE);

        $include = $includes[2];
        $this->assertEquals(3, count($include));
        $this->assertEquals(CommentSchema::REL_EMOTIONS, $include[2]->getNameInSchema());
        $this->assertEquals(CommentSchema::TYPE, $include[2]->getFromSchema()::TYPE);
        $this->assertEquals(EmotionSchema::TYPE, $include[2]->getToSchema()::TYPE);
    }

    /**
     * Test query.
     */
    public function testIncludesWithInvalidPaths(): void
    {
        $this->expectException(\Limoncello\Flute\Exceptions\InvalidQueryParametersException::class);

        $path1 = [BoardSchema::REL_POSTS . 'XXX']; // invalid path

        $includeParameters = [$path1];

        $mapper = $this->createMapper(BoardSchema::class)->withIncludes($includeParameters);
        $this->deepIterableToArray($mapper->getMappedIncludes());
    }

    /**
     * Test query.
     */
    public function testUsageWhenNoRootSchemaSet(): void
    {
        $this->expectException(\Limoncello\Flute\Exceptions\LogicException::class);

        $includes = (new ParametersMapper($this->createDefaultJsonSchemas()))->getMappedIncludes();

        $this->deepIterableToArray($includes);
    }

    /**
     * @param string $schemaClass
     *
     * @return ParametersMapperInterface
     */
    private function createMapper(string $schemaClass): ParametersMapperInterface
    {
        assert(in_array(SchemaInterface::class, class_implements($schemaClass)));

        /** @var SchemaInterface $schemaClass */

        return (new ParametersMapper($this->createDefaultJsonSchemas()))
            ->selectRootSchemaByResourceType($schemaClass::TYPE);
    }

    /**
     * @return JsonSchemasInterface
     */
    private function createDefaultJsonSchemas(): JsonSchemasInterface
    {
        return $this->getJsonSchemas(new Factory(new Container()), $this->getModelSchemas());
    }
}
