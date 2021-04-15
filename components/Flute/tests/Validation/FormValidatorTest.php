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

namespace Limoncello\Tests\Flute\Validation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Limoncello\Container\Container;
use Limoncello\Contracts\Data\ModelSchemaInfoInterface;
use Limoncello\Flute\Api\BasicRelationshipPaginationStrategy;
use Limoncello\Flute\Contracts\Api\RelationshipPaginationStrategyInterface;
use Limoncello\Flute\Contracts\FactoryInterface;
use Limoncello\Flute\Contracts\Validation\FormValidatorInterface;
use Limoncello\Flute\Factory;
use Limoncello\Flute\L10n\Messages;
use Limoncello\Flute\Validation\Form\Execution\FormRulesSerializer;
use Limoncello\Flute\Validation\Form\FormValidator;
use Limoncello\Tests\Flute\Data\L10n\FormatterFactory;
use Limoncello\Tests\Flute\Data\Models\Comment;
use Limoncello\Tests\Flute\Data\Types\SystemDateTimeType;
use Limoncello\Tests\Flute\Data\Types\SystemDateType;
use Limoncello\Tests\Flute\Data\Types\SystemUuidType;
use Limoncello\Tests\Flute\Data\Validation\Forms\CreateCommentRules;
use Limoncello\Tests\Flute\Data\Validation\Forms\UpdateCommentRules;
use Limoncello\Tests\Flute\TestCase;
use Limoncello\Validation\Execution\BlockSerializer;
use Limoncello\Validation\Execution\ContextStorage;

/**
 * @package Limoncello\Tests\Flute
 */
class FormValidatorTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Type::hasType(SystemDateTimeType::NAME) === true ?: Type::addType(SystemDateTimeType::NAME, SystemDateTimeType::class);
        Type::hasType(SystemDateType::NAME) === true ?: Type::addType(SystemDateType::NAME, SystemDateType::class);
        Type::hasType(SystemUuidType::NAME) === true ?: Type::addType(SystemUuidType::NAME, SystemUuidType::class);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testValidator(): void
    {
        $this->assertNotNull($validator = $this->createValidator(CreateCommentRules::class));

        $this->assertTrue($validator->validate([Comment::FIELD_TEXT => 'some text']));
        $this->assertFalse($validator->validate([Comment::FIELD_TEXT => false]));
        $this->assertEquals(
            [Comment::FIELD_TEXT => 'The value should be a string.'],
            $this->iterableToArray($validator->getMessages())
        );
        $this->assertTrue($validator->validate([Comment::FIELD_UUID => '64c7660d-01f6-406a-8d13-e137ce268fde']));
        $this->assertFalse($validator->validate([Comment::FIELD_UUID => '##1234']));
        $this->assertEquals(
            [Comment::FIELD_UUID => 'The value should be a valid UUID.'],
            $this->iterableToArray($validator->getMessages())
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInvalidInput(): void
    {
        $this->expectException(\Limoncello\Flute\Exceptions\InvalidArgumentException::class);

        $this->assertNotNull($validator = $this->createValidator(CreateCommentRules::class));

        $validator->validate('not array');

        $validator->validate([Comment::FIELD_UUID => new \stdClass()]);
    }

    /**
     * @return void
     *
     * @throws DBALException
     * @throws Exception
     */
    public function testReadableValidationRules(): void
    {
        $this->assertNotNull($validator = $this->createValidator(UpdateCommentRules::class));

        $this->assertTrue($validator->validate([Comment::REL_POST => '1']));
        $this->assertFalse($validator->validate([Comment::REL_POST => '-1', 'unknown_field' => 'some_value']));
        $this->assertEquals(
            [
                Comment::REL_POST => 'The value should be a valid identifier.',
                'unknown_field'   => 'The value is invalid.',
            ],
            $this->iterableToArray($validator->getMessages())
        );

        $this->assertTrue($validator->validate([Comment::REL_EMOTIONS => ['1', '2']]));
        $this->assertFalse($validator->validate([Comment::REL_EMOTIONS => ['1', '-2']]));
        $this->assertEquals(
            [Comment::REL_EMOTIONS => 'The value should be valid identifiers.'],
            $this->iterableToArray($validator->getMessages())
        );
    }

    /**
     * @param string $rulesClass
     *
     * @return FormValidatorInterface
     *
     * @throws Exception
     * @throws DBALException
     */
    private function createValidator(string $rulesClass): FormValidatorInterface
    {
        $serializer = new FormRulesSerializer(new BlockSerializer());
        $serializer->addRulesFromClass($rulesClass);

        $container                                                 = new Container();
        $container[FactoryInterface::class]                        = new Factory($container);
        $container[Connection::class]                              = $this->initDb();
        $container[ModelSchemaInfoInterface::class]                = $this->getModelSchemas();
        $container[RelationshipPaginationStrategyInterface::class] = new BasicRelationshipPaginationStrategy(30);

        $validator = new FormValidator(
            $rulesClass,
            FormRulesSerializer::class,
            $serializer->getData(),
            new ContextStorage($serializer->getBlocks(), $container),
            (new FormatterFactory())->createFormatter(Messages::NAMESPACE_NAME)
        );

        return $validator;
    }
}
