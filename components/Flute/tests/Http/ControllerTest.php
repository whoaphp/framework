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

namespace Limoncello\Tests\Flute\Http;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Limoncello\Container\Container;
use Limoncello\Contracts\Application\ApplicationConfigurationInterface;
use Limoncello\Contracts\Application\CacheSettingsProviderInterface;
use Limoncello\Contracts\Data\ModelSchemaInfoInterface;
use Limoncello\Contracts\L10n\FormatterFactoryInterface;
use Limoncello\Contracts\Settings\SettingsProviderInterface;
use Limoncello\Flute\Api\BasicRelationshipPaginationStrategy;
use Limoncello\Flute\Contracts\Api\RelationshipPaginationStrategyInterface;
use Limoncello\Flute\Contracts\Encoder\EncoderInterface;
use Limoncello\Flute\Contracts\FactoryInterface;
use Limoncello\Flute\Contracts\Http\Query\ParametersMapperInterface;
use Limoncello\Flute\Contracts\Schema\JsonSchemasInterface;
use Limoncello\Flute\Contracts\Validation\FormValidatorFactoryInterface;
use Limoncello\Flute\Contracts\Validation\JsonApiParserFactoryInterface;
use Limoncello\Flute\Factory;
use Limoncello\Flute\Http\Query\ParametersMapper;
use Limoncello\Flute\Package\FluteSettings;
use Limoncello\Flute\Validation\Form\Execution\FormValidatorFactory;
use Limoncello\Flute\Validation\JsonApi\Execution\JsonApiParserFactory;
use Limoncello\Tests\Flute\Data\Api\CommentsApi;
use Limoncello\Tests\Flute\Data\Http\ApiBoardsController;
use Limoncello\Tests\Flute\Data\Http\ApiCategoriesController;
use Limoncello\Tests\Flute\Data\Http\ApiCommentsControllerApi;
use Limoncello\Tests\Flute\Data\Http\ApiPostsController;
use Limoncello\Tests\Flute\Data\Http\ApiUsersController;
use Limoncello\Tests\Flute\Data\Http\FormCommentsController;
use Limoncello\Tests\Flute\Data\L10n\FormatterFactory;
use Limoncello\Tests\Flute\Data\Models\Board;
use Limoncello\Tests\Flute\Data\Models\Comment;
use Limoncello\Tests\Flute\Data\Models\CommentEmotion;
use Limoncello\Tests\Flute\Data\Models\Post;
use Limoncello\Tests\Flute\Data\Package\CacheSettingsProvider;
use Limoncello\Tests\Flute\Data\Package\Flute;
use Limoncello\Tests\Flute\Data\Schemas\BoardSchema;
use Limoncello\Tests\Flute\Data\Schemas\CategorySchema;
use Limoncello\Tests\Flute\Data\Schemas\CommentSchema;
use Limoncello\Tests\Flute\Data\Schemas\EmotionSchema;
use Limoncello\Tests\Flute\Data\Schemas\PostSchema;
use Limoncello\Tests\Flute\Data\Schemas\UserSchema;
use Limoncello\Tests\Flute\Data\Types\SystemDateTimeType;
use Limoncello\Tests\Flute\Data\Types\SystemDateType;
use Limoncello\Tests\Flute\Data\Types\SystemUuidType;
use Limoncello\Tests\Flute\TestCase;
use Mockery;
use Mockery\Mock;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Uri;

/**
 * @package Limoncello\Tests\Flute
 */
class ControllerTest extends TestCase
{
    const DEFAULT_JSON_META = [
        'Title' => 'Default JSON API meta information',
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // If test is run withing the whole test suite then those lines not needed, however
        // if only tests from this file are run then the lines are required.
        Type::hasType(SystemDateTimeType::NAME) === true ?: Type::addType(SystemDateTimeType::NAME, SystemDateTimeType::class);
        Type::hasType(SystemDateType::NAME) === true ?: Type::addType(SystemDateType::NAME, SystemDateType::class);

        Type::hasType(SystemUuidType::NAME) === true ?: Type::addType(SystemUuidType::NAME, SystemUuidType::class);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithoutParameters(): void
    {
        $routeParams = [];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn([]);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        $this->assertEquals(
            'http://localhost.local/comments?page[offset]=10&page[limit]=10',
            urldecode($resource[DocumentInterface::KEYWORD_LINKS][DocumentInterface::KEYWORD_NEXT])
        );
        $this->assertCount(10, $resource[DocumentInterface::KEYWORD_DATA]);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexSortByIdDesc(): void
    {
        $routeParams = [];
        $container   = $this->createContainer();
        $queryParams = [
            'sort' => '-' . CommentSchema::RESOURCE_ID,
        ];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body    = (string)($response->getBody());
        $decoded = json_decode($body, true);

        $this->assertEquals(self::DEFAULT_JSON_META, $decoded[DocumentInterface::KEYWORD_META]);
        $this->assertEquals(
            'http://localhost.local/comments?sort=-id&page[offset]=10&page[limit]=10',
            urldecode($decoded[DocumentInterface::KEYWORD_LINKS][DocumentInterface::KEYWORD_NEXT])
        );
        $this->assertCount(10, $resources = $decoded[DocumentInterface::KEYWORD_DATA]);

        // check IDs are in descending order
        $allDesc = true;
        for ($index = 1; $index < count($resources); $index++) {
            if ($resources[$index]['id'] > $resources[$index - 1]['id']) {
                $allDesc = false;
                break;
            }
        }
        $this->assertTrue($allDesc);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithParameters(): void
    {
        $routeParams = [];
        $queryParams = [
            'filter'  => [
                CommentSchema::RESOURCE_ID => [
                    'in' => '10,11,15,17,21',
                ],
                CommentSchema::ATTR_TEXT   => [
                    'like' => '%',
                ],
                CommentSchema::REL_POST    => [
                    'in' => '8,11,15',
                ],
            ],
            'sort'    => CommentSchema::REL_POST,
            'include' => CommentSchema::REL_USER,
            'fields'  => [
                CommentSchema::TYPE =>
                    CommentSchema::ATTR_TEXT . ',' . CommentSchema::REL_USER . ',' . CommentSchema::REL_POST,
            ],
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // check reply has resource included
        $this->assertCount(1, $resource[DocumentInterface::KEYWORD_INCLUDED]);
        // manually checked it should be 1 rows selected
        $this->assertCount(1, $resource[DocumentInterface::KEYWORD_DATA]);
        // check response sorted by post.id
        $this->assertEquals(8, $resource['data'][0]['relationships'][CommentSchema::REL_POST]['data']['id']);

        // check some fields were filtered out
        $this->assertFalse(isset($resource['data'][0]['relationships'][CommentSchema::REL_EMOTIONS]));

        // check dynamic attribute is present in User
        $this->assertTrue(isset(
            $resource[DocumentInterface::KEYWORD_INCLUDED][0]
            [DocumentInterface::KEYWORD_ATTRIBUTES][UserSchema::D_ATTR_FULL_NAME]
        ));
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithParametersJoinedByOR(): void
    {
        $routeParams = [];
        $queryParams = [
            'filter' => [
                'or' => [
                    CommentSchema::RESOURCE_ID => [
                        'in' => '10,11',
                    ],
                    // ID 11 has 'quo' in 'text' and we will check it won't be returned twice
                    CommentSchema::ATTR_TEXT   => [
                        'like' => '%quo%',
                    ],
                ],
            ],
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // manually checked it should be 7 rows selected
        $this->assertCount(7, $resource[DocumentInterface::KEYWORD_DATA]);
        $this->assertEquals(8, $resource['data'][0]['id']);
        $this->assertEquals(51, $resource['data'][1]['id']);
        $this->assertEquals(57, $resource['data'][2]['id']);
        $this->assertEquals(59, $resource['data'][3]['id']);
        $this->assertEquals(66, $resource['data'][4]['id']);
        $this->assertEquals(69, $resource['data'][5]['id']);
        $this->assertEquals(96, $resource['data'][6]['id']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithParametersWithInvalidJoinParam(): void
    {
        $routeParams = [];
        $queryParams = [
            'filter' => [
                'or'  => [
                    CommentSchema::RESOURCE_ID => [
                        'in' => '10,11',
                    ],
                ],
                'xxx' => 'only one top-level element is allowed if AND/OR is used',
            ],
        ];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);


        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            ApiCommentsControllerApi::index($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);

        $errors = $exception->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(['parameter' => 'filter'], $errors[0]->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithInvalidParameters(): void
    {
        $routeParams = [];
        $queryParams = [
            'filter'  => [
                'aaa' => [
                    'in' => '10,11',
                ],
            ],
            'sort'    => 'bbb',
            'include' => 'ccc',
        ];
        $uri         = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            ApiBoardsController::index($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);

        $this->assertCount(1, $exception->getErrors());
        $this->assertEquals(['parameter' => 'filter'], $exception->getErrors()->getArrayCopy()[0]->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testPaginationInRelationship(): void
    {
        $routeParams = [];
        $queryParams = [
            'include' => BoardSchema::REL_POSTS,
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/boards?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        // replace paging strategy to get paginated results in the relationship
        $container[RelationshipPaginationStrategyInterface::class] = new BasicRelationshipPaginationStrategy(3);

        $response = ApiBoardsController::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // check reply has resource included
        $this->assertCount(13, $resource[DocumentInterface::KEYWORD_INCLUDED]);
        $this->assertCount(5, $resource[DocumentInterface::KEYWORD_DATA]);
        // check response sorted by post.id
        $this->assertEquals(1, $resource['data'][0]['id']);
        $this->assertEquals(2, $resource['data'][1]['id']);
        $this->assertEquals(3, $resource['data'][2]['id']);

        // manually checked that one of the elements should have paginated data in relationship
        $this->assertTrue(isset($resource['data'][2]['relationships']['posts-relationship']['links']['next']));
        $link = $resource['data'][2]['relationships']['posts-relationship']['links']['next'];
        $this->assertEquals('/boards/3/relationships/posts-relationship?offset=3&limit=3', $link);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIncludeNullableRelationshipToItself(): void
    {
        $routeParams = [];
        $queryParams = [
            'include' => CategorySchema::REL_PARENT . ',' . CategorySchema::REL_CHILDREN,
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/categories?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCategoriesController::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body      = (string)($response->getBody());
        $resources = json_decode($body, true);

        // manually checked it should be 4 rows selected
        $this->assertCount(3, $resources[DocumentInterface::KEYWORD_DATA]);
        // check response sorted by post.id
        // $this->assertNull($resources['data'][0]['relationships']['parent-relationship']['data']['id']);
        $this->assertNull($resources['data'][0]['relationships']['parent-relationship']['data']);
        $this->assertCount(2, $resources['data'][0]['relationships']['children-relationship']['data']);
        $this->assertEquals(1, $resources['data'][1]['relationships']['parent-relationship']['data']['id']);
        $this->assertCount(0, $resources['data'][1]['relationships']['children-relationship']['data']);
        $this->assertEquals(1, $resources['data'][2]['relationships']['parent-relationship']['data']['id']);
        $this->assertCount(0, $resources['data'][2]['relationships']['children-relationship']['data']);
    }

    /**
     * Controller test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     * @throws Exception
     */
    public function testReadToOneRelationship(): void
    {
        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => '2'];
        $queryParams = [];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/comments/2/users?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        // replace paging strategy to get paginated results in the relationship
        $container[RelationshipPaginationStrategyInterface::class] = new BasicRelationshipPaginationStrategy(3);

        $response = ApiCommentsControllerApi::readUser($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        $this->assertNotEmpty($resource);
        $this->assertEquals(5, $resource['data']['id']);
    }

    /**
     * Controller test.
     *
     * @throws DBALException
     * @throws Exception
     */
    public function testIndexWithHasManyFilter(): void
    {
        $routeParams = [];
        $queryParams = [
            'filter' => [
                UserSchema::REL_COMMENTS => [
                    'gt' => '1',
                    'lt' => '10',
                ],
                UserSchema::REL_POSTS    => [
                    'gt' => '1',
                    'lt' => '7',
                ],
            ],
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/users?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiUsersController::index($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // manually checked it should be 2 rows
        // - comments with ID from 1 to 10 have user IDs 2, 4, 5
        // - posts with ID from 1 to 7 have user IDs 3, 4, 5
        // - therefore output must have 2 users with IDs 4 and 5
        $this->assertCount(3, $resource[DocumentInterface::KEYWORD_DATA]);
        $this->assertEquals(3, $resource['data'][0]['id']);
        $this->assertEquals(4, $resource['data'][1]['id']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testIndexWithBelongsToManyFilter(): void
    {
        $routeParams = [];
        // comments with ID 2 and 4 have more than 1 emotions. We will check that only distinct rows to be returned.
        $queryParams = [
            'filter' => [
                CommentSchema::RESOURCE_ID  => [
                    'in' => '2,3,4',
                ],
                CommentSchema::REL_EMOTIONS => [
                    'in' => '2,3,4',
                ],
            ],
        ];
        $container   = $this->createContainer();
        $uri         = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        try {
            // disable index filtering for this test
            CommentsApi::$isFilterIndexForCurrentUser = false;

            $response = ApiCommentsControllerApi::index($routeParams, $container, $request);
        } finally {
            CommentsApi::$isFilterIndexForCurrentUser = CommentsApi::DEBUG_KEY_DEFAULT_FILTER_INDEX;
        }
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // manually checked if rows are not distinct it would be 6 rows
        $this->assertCount(3, $resource[DocumentInterface::KEYWORD_DATA]);
        $this->assertEquals(2, $resource['data'][0]['id']);
        $this->assertEquals(3, $resource['data'][1]['id']);
        $this->assertEquals(4, $resource['data'][2]['id']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreate(): void
    {
        $uuid      = '64c7660d-01f6-406a-8d13-e137ce268fde';
        $text      = 'Some comment text';
        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "comments",
                "id"    : null,
                "attributes" : {
                    "uuid-attribute" : "$uuid",
                    "text-attribute" : "$text"
                },
                "relationships" : {
                    "post-relationship" : {
                        "data" : { "type" : "posts", "id" : "1" }
                    },
                    "emotions-relationship" : {
                        "data" : [
                            { "type": "emotions", "id":"2" },
                            { "type": "emotions", "id":"3" }
                        ]
                    }
                }
            }
        }
EOT;

        $routeParams = [];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        //$request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn([]);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        // check the item is not in the database
        $tableName = Comment::TABLE_NAME;
        $idColumn  = Comment::FIELD_ID;
        $index     = '101';
        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $this->assertEmpty($connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $index")->fetch());

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::create($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['http://localhost.local/comments/101'], $response->getHeader('Location'));
        $this->assertNotEmpty((string)($response->getBody()));

        // check the item is in the database
        $this->assertNotEmpty($connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $index")->fetch());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateDuplicate(): void
    {
        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // existing role name
        $boardsTable  = Board::TABLE_NAME;
        $boardsTitle  = Board::FIELD_TITLE;
        $existingName = $connection
            ->executeQuery("SELECT $boardsTitle FROM $boardsTable LIMIT 1")->fetchColumn();

        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "boards",
                "attributes" : {
                    "title-attribute" : "$existingName"
                }
            }
        }
EOT;

        $routeParams = [];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        //$request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn([]);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            ApiBoardsController::create($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertCount(1, $exception->getErrors());
        $this->assertSame('409', $exception->getErrors()->offsetGet(0)->getStatus());
    }

    /**
     * Controller test (form validator).
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testFormCreate(): void
    {
        $routeParams = [];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        /** @var ServerRequestInterface $request */

        $container = $this->createContainer();

        $response = FormCommentsController::create($routeParams, $container, $request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testReadWithoutParameters(): void
    {
        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => '96'];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn([]);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::read($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true)[DocumentInterface::KEYWORD_DATA];

        $this->assertEquals('comments', $resource['type']);
        $this->assertEquals('96', $resource['id']);
        $this->assertEquals([
            'user-relationship'     => ['data' => ['type' => 'users', 'id' => '1']],
            'post-relationship'     => ['data' => ['type' => 'posts', 'id' => '12']],
            'emotions-relationship' => ['links' => ['self' => '/comments/96/relationships/emotions-relationship']],
        ], $resource['relationships']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testUpdate(): void
    {
        $uuid      = '64c7660d-01f6-406a-8d13-e137ce268fde';
        $text      = 'Some comment text';
        $index     = '96';
        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "comments",
                "attributes" : {
                    "uuid-attribute" : "$uuid",
                    "text-attribute" : "$text"
                },
                "relationships" : {
                    "post-relationship" : {
                        "data" : { "type" : "posts", "id" : "1" }
                    },
                    "emotions-relationship" : {
                        "data" : [
                            { "type": "emotions", "id":"2" },
                            { "type": "emotions", "id":"3" }
                        ]
                    }
                }
            }
        }
EOT;

        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $container = $this->createContainer();
        $response  = ApiCommentsControllerApi::update($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNotEmpty($body = (string)($response->getBody()));
        $resource = json_decode($body, true)[DocumentInterface::KEYWORD_DATA];
        $this->assertEquals($text, $resource[DocumentInterface::KEYWORD_ATTRIBUTES][CommentSchema::ATTR_TEXT]);
        $this->assertNotEmpty($resource[DocumentInterface::KEYWORD_ATTRIBUTES][CommentSchema::ATTR_UPDATED_AT]);

        // check the item is in the database
        $tableName = Comment::TABLE_NAME;
        $idColumn  = Comment::FIELD_ID;
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $this->assertNotEmpty(
            $row = $connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $index")->fetch()
        );
        $this->assertEquals(1, $row[Comment::FIELD_ID_POST]);
        $tableName  = CommentEmotion::TABLE_NAME;
        $idColumn   = CommentEmotion::FIELD_ID_COMMENT;
        $columnName = CommentEmotion::FIELD_ID_EMOTION;
        $emotionIds =
            $connection->executeQuery("SELECT $columnName FROM $tableName WHERE $idColumn = $index")->fetchAll();
        $this->assertEquals(
            [[CommentEmotion::FIELD_ID_EMOTION => '2'], [CommentEmotion::FIELD_ID_EMOTION => '3']],
            $emotionIds
        );
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateDuplicate(): void
    {
        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // existing role name
        $boardsTable  = Board::TABLE_NAME;
        $first2boards = $connection->executeQuery("SELECT * FROM $boardsTable LIMIT 2")->fetchAll();
        $this->assertEquals(2, count($first2boards));
        $firstId     = $first2boards[0][Board::FIELD_ID];
        $secondTitle = $first2boards[1][Board::FIELD_TITLE];

        $jsonInput = <<<EOT
        {
            "data" : {
                "type" : "boards",
                "id"   : "$firstId",
                "attributes" : {
                    "title-attribute" : "$secondTitle"
                }
            }
        }
EOT;

        $routeParams = [ApiBoardsController::ROUTE_KEY_INDEX => $firstId];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        //$request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn([]);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            ApiBoardsController::update($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertCount(1, $exception->getErrors());
        $this->assertSame('409', $exception->getErrors()->offsetGet(0)->getStatus());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testUpdateNonExistingItem(): void
    {
        $text      = 'Some comment text';
        $index     = '-1';
        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "comments",
                "id"    : "$index",
                "attributes" : {
                    "text-attribute" : "$text"
                }
            }
        }
EOT;

        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $container = $this->createContainer();
        $exception = null;
        try {
            ApiCommentsControllerApi::update($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);

        $errors = $exception->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(['pointer' => '/data/id'], $errors[0]->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testUpdateNonMatchingIndexes(): void
    {
        $text      = 'Some comment text';
        $index1    = '1';
        $index2    = '2';
        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "comments",
                "id"    : "$index1",
                "attributes" : {
                    "text-attribute" : "$text"
                }
            }
        }
EOT;

        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index2];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $container = $this->createContainer();
        $exception = null;
        try {
            ApiCommentsControllerApi::update($routeParams, $container, $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);

        $errors = $exception->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(['pointer' => '/data/id'], $errors[0]->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testSendInvalidInput(): void
    {
        $index     = '1';
        $jsonInput = '{';

        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            ApiCommentsControllerApi::update($routeParams, $this->createContainer(), $request);
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);

        $this->assertCount(1, $errors = $exception->getErrors());
        $this->assertEquals(['pointer' => '/data'], $errors[0]->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testUpdateForNonExistingItem(): void
    {
        $text      = 'Some comment text';
        $index     = '99999999'; // non-existing
        $jsonInput = <<<EOT
        {
            "data" : {
                "type"  : "users",
                "id"    : "$index",
                "attributes" : {
                    "first-name-attribute" : "$text"
                }
            }
        }
EOT;

        $routeParams = [ApiUsersController::ROUTE_KEY_INDEX => $index];
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($jsonInput);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $response = ApiUsersController::update($routeParams, $this->createContainer(), $request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testDelete(): void
    {
        $tableName = Comment::TABLE_NAME;
        $idColumn  = Comment::FIELD_ID;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // add comment to delete
        $this->assertEquals(1, $connection->insert($tableName, [
            Comment::FIELD_UUID       => '64c7660d-01f6-406a-8d13-e137ce268fde',
            Comment::FIELD_TEXT       => 'Some text',
            Comment::FIELD_ID_USER    => '1',
            Comment::FIELD_ID_POST    => '2',
            Comment::FIELD_CREATED_AT => '2000-01-02',
        ]));
        $index = $connection->lastInsertId();

        // check the item is in the database
        $this->assertNotEmpty($connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $index")->fetch());

        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri('http://localhost.local/comments'));

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::delete($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(204, $response->getStatusCode());

        // check the item is not in the database
        $this->assertFalse($connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $index")->fetch());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testReadRelationship(): void
    {
        $index       = '2';
        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];
        $queryParams = [
            'sort' => '+' . EmotionSchema::ATTR_NAME,
        ];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments/relationships/emotions?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::readEmotions($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        $this->assertCount(4, $resource[DocumentInterface::KEYWORD_DATA]);
        // manually checked that emotions should have these ids and sorted by name in ascending order.
        $this->assertEquals('2', $resource['data'][0]['id']);
        $this->assertEquals('3', $resource['data'][1]['id']);
        $this->assertEquals('4', $resource['data'][2]['id']);
        $this->assertEquals('5', $resource['data'][3]['id']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testReadRelationshipIdentifiers(): void
    {
        $index       = '2';
        $routeParams = [ApiCommentsControllerApi::ROUTE_KEY_INDEX => $index];
        $queryParams = [
            'sort' => '+' . EmotionSchema::ATTR_NAME,
        ];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments/relationships/emotions?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::readEmotionsIdentifiers($routeParams, $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        $this->assertCount(4, $resource[DocumentInterface::KEYWORD_DATA]);
        // manually checked that emotions should have these ids and sorted by name in ascending order.
        $this->assertEquals('2', $resource['data'][0]['id']);
        $this->assertEquals('3', $resource['data'][1]['id']);
        $this->assertEquals('4', $resource['data'][2]['id']);
        $this->assertEquals('5', $resource['data'][3]['id']);

        // check we have only IDs in response (no attributes)
        $this->assertArrayNotHasKey(DocumentInterface::KEYWORD_ATTRIBUTES, $resource['data'][0]);
        $this->assertArrayNotHasKey(DocumentInterface::KEYWORD_ATTRIBUTES, $resource['data'][1]);
        $this->assertArrayNotHasKey(DocumentInterface::KEYWORD_ATTRIBUTES, $resource['data'][2]);
        $this->assertArrayNotHasKey(DocumentInterface::KEYWORD_ATTRIBUTES, $resource['data'][3]);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testAddInRelationship(): void
    {
        $intTable     = CommentEmotion::TABLE_NAME;
        $intCommentFk = CommentEmotion::FIELD_ID_COMMENT;
        $intEmotionFk = CommentEmotion::FIELD_ID_EMOTION;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $commentId = 1;
        $emotionId = 3;
        // check the item is in the database
        $this->assertEmpty($connection->executeQuery(
            "SELECT * FROM $intTable WHERE $intCommentFk = $commentId AND $intEmotionFk = $emotionId"
        )->fetchAll());

        $routeParams = [
            ApiCommentsControllerApi::ROUTE_KEY_INDEX => $commentId,
        ];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $uri     = "http://localhost.local/posts/$commentId/relationships/emotions";
        $request->shouldReceive('getUri')->twice()->withNoArgs()->andReturn(new Uri($uri));

        $requestBody = <<<EOT
        {
            "data": [
                { "type": "emotions", "id": "$emotionId" }
            ]
        }
EOT;
        $request->shouldReceive('getBody')->twice()->withNoArgs()->andReturn($requestBody);

        /** @var ServerRequestInterface $request */

        $this->assertNotNull($response = ApiCommentsControllerApi::addEmotions($routeParams, $container, $request));
        $this->assertEquals(204, $response->getStatusCode());

        // check the item is in the database
        $this->assertNotEmpty($connection->executeQuery(
            "SELECT * FROM $intTable WHERE $intCommentFk = $commentId AND $intEmotionFk = $emotionId"
        )->fetchAll());

        // try to add same relationship second time (server must return a successful response)
        $this->assertNotNull($response = ApiCommentsControllerApi::addEmotions($routeParams, $container, $request));
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testAddInRelationshipInvalidId(): void
    {
        $intTable     = CommentEmotion::TABLE_NAME;
        $intCommentFk = CommentEmotion::FIELD_ID_COMMENT;
        $intEmotionFk = CommentEmotion::FIELD_ID_EMOTION;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $commentId = 1;
        $emotionId = 123456;
        // check the item is in the database
        $this->assertEmpty($connection->executeQuery(
            "SELECT * FROM $intTable WHERE $intCommentFk = $commentId AND $intEmotionFk = $emotionId"
        )->fetchAll());

        $routeParams = [
            ApiCommentsControllerApi::ROUTE_KEY_INDEX => $commentId,
        ];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $uri     = "http://localhost.local/posts/$commentId/relationships/emotions";
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri($uri));

        $requestBody = <<<EOT
        {
            "data": [
                { "type": "emotions", "id": "$emotionId" }
            ]
        }
EOT;
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($requestBody);

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            $this->assertNotNull(ApiCommentsControllerApi::addEmotions($routeParams, $container, $request));
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertCount(1, $exception->getErrors());
        $error = $exception->getErrors()->getArrayCopy()[0];
        $this->assertEquals(['pointer' => '/data/relationships/emotions-relationship'], $error->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testDeleteInRelationship(): void
    {
        $intTable     = CommentEmotion::TABLE_NAME;
        $intCommentFk = CommentEmotion::FIELD_ID_COMMENT;
        $intEmotionFk = CommentEmotion::FIELD_ID_EMOTION;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $commentId = 1;
        $emotionId = 4;
        // check the item is in the database
        $this->assertNotEmpty($connection->executeQuery(
            "SELECT * FROM $intTable WHERE $intCommentFk = $commentId AND $intEmotionFk = $emotionId"
        )->fetchAll());

        $routeParams = [
            ApiCommentsControllerApi::ROUTE_KEY_INDEX => $commentId,
        ];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $uri     = "http://localhost.local/posts/$commentId/relationships/emotions";
        $request->shouldReceive('getUri')->twice()->withNoArgs()->andReturn(new Uri($uri));

        $requestBody = <<<EOT
        {
            "data": [
                { "type": "emotions", "id": "$emotionId" }
            ]
        }
EOT;
        $request->shouldReceive('getBody')->twice()->withNoArgs()->andReturn($requestBody);

        /** @var ServerRequestInterface $request */

        $this->assertNotNull($response = ApiCommentsControllerApi::deleteEmotions($routeParams, $container, $request));
        $this->assertEquals(204, $response->getStatusCode());

        // check the item is not in the database
        $this->assertEmpty($connection->executeQuery(
            "SELECT * FROM $intTable WHERE $intCommentFk = $commentId AND $intEmotionFk = $emotionId"
        )->fetchAll());

        // calling `delete` on non-existing resource should not cause any errors
        $this->assertNotNull($response = ApiCommentsControllerApi::deleteEmotions($routeParams, $container, $request));
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testReplaceInRelationship(): void
    {
        $tableName = Post::TABLE_NAME;
        $idColumn  = Post::FIELD_ID;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // add comment to update
        $postId      = 2;
        $newEditorId = 1;

        // check old editor value do not match the new one
        $this->assertNotEmpty(
            $post = $connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $postId")->fetch()
        );
        $this->assertNotEquals($newEditorId, $post[Post::FIELD_ID_EDITOR]);

        $routeParams = [
            ApiCommentsControllerApi::ROUTE_KEY_INDEX => $postId,
        ];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $uri     = "http://localhost.local/posts/$postId/relationships/editor";
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri($uri));

        $requestBody = <<<EOT
        {
            "data": { "type": "users", "id": "1" }
        }
EOT;
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($requestBody);

        /** @var ServerRequestInterface $request */

        $this->assertNotNull($response = ApiPostsController::replaceEditor($routeParams, $container, $request));
        $this->assertEquals(200, $response->getStatusCode());

        // check the value has been changed
        $this->assertNotEmpty(
            $post = $connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $postId")->fetch()
        );
        $this->assertEquals($newEditorId, $post[Post::FIELD_ID_EDITOR]);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function testReplaceInRelationshipInvalidId(): void
    {
        $tableName = Post::TABLE_NAME;
        $idColumn  = Post::FIELD_ID;

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // add comment to update
        $postId      = 2;
        $newEditorId = 1;

        // check old editor value do not match the new one
        $this->assertNotEmpty(
            $post = $connection->executeQuery("SELECT * FROM $tableName WHERE $idColumn = $postId")->fetch()
        );
        $this->assertNotEquals($newEditorId, $post[Post::FIELD_ID_EDITOR]);

        $routeParams = [
            ApiCommentsControllerApi::ROUTE_KEY_INDEX => $postId,
        ];

        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $uri     = "http://localhost.local/posts/$postId/relationships/editor";
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn(new Uri($uri));

        $requestBody = <<<EOT
        {
            "data": { "type": "users-XYZ", "id": "1" }
        }
EOT;
        $request->shouldReceive('getBody')->once()->withNoArgs()->andReturn($requestBody);

        /** @var ServerRequestInterface $request */

        $exception = null;
        try {
            $this->assertNotNull(ApiPostsController::replaceEditor($routeParams, $container, $request));
        } catch (JsonApiException $exception) {
        }
        $this->assertNotNull($exception);
        $this->assertCount(1, $exception->getErrors());
        $error = $exception->getErrors()->getArrayCopy()[0];
        $this->assertEquals('The value should be a valid JSON API relationship type.', $error->getDetail());
        $this->assertEquals(['pointer' => '/data/relationships/editor-relationship'], $error->getSource());
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testFilterBelongsToRelationship(): void
    {
        /** @noinspection SpellCheckingInspection */
        $seldomWord  = 'perspiciatis';
        $queryParams = [
            'filter' => [
                CommentSchema::REL_POST . '.' . PostSchema::ATTR_TEXT => [
                    'like' => "%$seldomWord%",
                ],
            ],
        ];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index([], $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        $this->assertCount(1, $resource[DocumentInterface::KEYWORD_DATA]);
        // manually checked that only 1 comment (ID=17) of current user has post (ID=15) text with $seldomWord
        $this->assertEquals('57', $resource['data'][0]['id']);
        // $this->assertContains('15', $resource['data'][0]['relationships'][CommentSchema::REL_POST]['data']['id']);
        $this->assertEquals('2', $resource['data'][0]['relationships'][CommentSchema::REL_POST]['data']['id']);
    }

    /**
     * Controller test.
     *
     * @throws Exception
     * @throws DBALException
     */
    public function testFilterBelongsToManyRelationship(): void
    {
        $seldomWord  = 'nostrum';
        $queryParams = [
            'filter' => [
                CommentSchema::REL_EMOTIONS . '.' . EmotionSchema::ATTR_NAME => [
                    'like' => "%$seldomWord%",
                ],
            ],
        ];
        $container   = $this->createContainer();
        /** @var Mock $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')->once()->withNoArgs()->andReturn($queryParams);
        $uri = new Uri('http://localhost.local/comments?' . http_build_query($queryParams));
        $request->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);

        /** @var ServerRequestInterface $request */

        $response = ApiCommentsControllerApi::index([], $container, $request);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $body     = (string)($response->getBody());
        $resource = json_decode($body, true);

        // manually checked it should be 8 comments with IDs below
        $this->assertCount(8, $resource[DocumentInterface::KEYWORD_DATA]);
        $this->assertEquals('14', $resource['data'][0]['id']);
        $this->assertEquals('15', $resource['data'][1]['id']);
        $this->assertEquals('17', $resource['data'][2]['id']);
        $this->assertEquals('51', $resource['data'][3]['id']);
        $this->assertEquals('55', $resource['data'][4]['id']);
        $this->assertEquals('66', $resource['data'][5]['id']);
        $this->assertEquals('68', $resource['data'][6]['id']);
        $this->assertEquals('83', $resource['data'][7]['id']);
    }

    /**
     * @return ContainerInterface
     *
     * @throws Exception
     * @throws DBALException
     */
    protected function createContainer(): ContainerInterface
    {
        $container = new Container();

        $container[FactoryInterface::class]          = $factory = new Factory($container);
        $container[FormatterFactoryInterface::class] = $formatterFactory = new FormatterFactory();
        $container[ModelSchemaInfoInterface::class]  = $modelSchemas = $this->getModelSchemas();

        $container[JsonSchemasInterface::class] = $jsonSchemas = $this->getJsonSchemas($factory, $modelSchemas);

        $container[ParametersMapperInterface::class] = function (PsrContainerInterface $container) {
            return new ParametersMapper($container->get(JsonSchemasInterface::class));
        };

        $container[Connection::class]                              = $connection = $this->initDb();
        $container[RelationshipPaginationStrategyInterface::class] = new BasicRelationshipPaginationStrategy(10);

        $appConfig = [
            ApplicationConfigurationInterface::KEY_ROUTES_FOLDER          =>
                implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Data', 'Http']),
            ApplicationConfigurationInterface::KEY_WEB_CONTROLLERS_FOLDER =>
                implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Data', 'Http']),
        ];
        [$modelToSchemaMap] = $this->getSchemaMap();
        $cacheSettingsProvider                            = new CacheSettingsProvider(
            $appConfig,
            [
                FluteSettings::class => (new Flute(
                    $modelToSchemaMap,
                    $this->getJsonValidationRuleSets(),
                    $this->getFormValidationRuleSets(),
                    $this->getQueryValidationRuleSets()
                ))->get($appConfig),
            ]
        );
        $container[CacheSettingsProviderInterface::class] = $cacheSettingsProvider;
        $container[SettingsProviderInterface::class]      = $cacheSettingsProvider;

        $container[EncoderInterface::class] =
            function (ContainerInterface $container) use ($factory, $jsonSchemas) {
                /** @var SettingsProviderInterface $provider */
                $provider = $container->get(SettingsProviderInterface::class);
                $settings = $provider->get(FluteSettings::class);

                // meta info so we can test it adds custom properties successfully
                $settings[FluteSettings::KEY_META] = static::DEFAULT_JSON_META;

                $urlPrefix = (string)$settings[FluteSettings::KEY_URI_PREFIX];
                $encoder   = $factory
                    ->createEncoder($jsonSchemas)
                    ->withEncodeOptions($settings[FluteSettings::KEY_JSON_ENCODE_OPTIONS])
                    ->withEncodeDepth($settings[FluteSettings::KEY_JSON_ENCODE_DEPTH])
                    ->withUrlPrefix($urlPrefix);
                if (isset($settings[FluteSettings::KEY_META]) === true) {
                    $meta = $settings[FluteSettings::KEY_META];
                    $encoder->withMeta($meta);
                }
                if (isset($settings[FluteSettings::KEY_IS_SHOW_VERSION]) === true &&
                    $settings[FluteSettings::KEY_IS_SHOW_VERSION] === true
                ) {
                    $encoder->withJsonApiVersion(FluteSettings::DEFAULT_JSON_API_VERSION);
                }

                return $encoder;
            };

        $container[JsonApiParserFactoryInterface::class] = function (ContainerInterface $container) {
            $factory = new JsonApiParserFactory($container);

            return $factory;
        };

        $container[FormValidatorFactoryInterface::class] = function (ContainerInterface $container) {
            $factory = new FormValidatorFactory($container);

            return $factory;
        };

        return $container;
    }
}
