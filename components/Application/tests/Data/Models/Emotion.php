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

namespace Whoa\Tests\Application\Data\Models;

use Doctrine\DBAL\Types\Type;
use Whoa\Contracts\Data\RelationshipTypes;

/**
 * @package Whoa\Tests\Application
 */
class Emotion extends Model
{
    /** @inheritdoc */
    const TABLE_NAME = 'emotions';

    /** @inheritdoc */
    const FIELD_ID = 'id_emotion';

    /** Relationship name */
    const REL_COMMENTS = 'comments';

    /** Field name */
    const FIELD_NAME = 'name';

    /**
     * @inheritdoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID => Type::INTEGER,
            self::FIELD_NAME => Type::STRING,
            self::FIELD_CREATED_AT => Type::DATETIME,
            self::FIELD_UPDATED_AT => Type::DATETIME,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_NAME => 255,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO_MANY => [
                self::REL_COMMENTS => [
                    Comment::class,
                    CommentEmotion::TABLE_NAME,
                    CommentEmotion::FIELD_ID_EMOTION,
                    CommentEmotion::FIELD_ID_COMMENT,
                    Comment::REL_EMOTIONS,
                ],
            ],
        ];
    }
}
