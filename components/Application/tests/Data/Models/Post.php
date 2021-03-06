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
class Post extends Model
{
    /** @inheritdoc */
    const TABLE_NAME = 'posts';

    /** @inheritdoc */
    const FIELD_ID = 'id_post';

    /** Field name */
    const FIELD_ID_BOARD = 'id_board_fk';

    /** Field name */
    const FIELD_ID_USER = 'id_user_fk';

    /** Field name */
    const FIELD_ID_EDITOR = 'id_editor_fk';

    /** Relationship name */
    const REL_BOARD = 'board';

    /** Relationship name */
    const REL_USER = 'user';

    /** Relationship name */
    const REL_EDITOR = 'editor';

    /** Relationship name */
    const REL_COMMENTS = 'comments';

    /** Field name */
    const FIELD_TITLE = 'title';

    /** Field name */
    const FIELD_TEXT = 'text';

    /** Length constant */
    const LENGTH_TITLE = 255;

    /**
     * @inheritdoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID => Type::INTEGER,
            self::FIELD_ID_BOARD => Type::INTEGER,
            self::FIELD_ID_USER => Type::INTEGER,
            self::FIELD_ID_EDITOR => Type::INTEGER,
            self::FIELD_TITLE => Type::STRING,
            self::FIELD_TEXT => Type::TEXT,
            self::FIELD_CREATED_AT => Type::DATETIME,
            self::FIELD_UPDATED_AT => Type::DATETIME,
            self::FIELD_DELETED_AT => Type::DATETIME,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_TITLE => self::LENGTH_TITLE,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO => [
                self::REL_USER => [User::class, self::FIELD_ID_USER, User::REL_AUTHORED_POSTS],
                self::REL_EDITOR => [User::class, self::FIELD_ID_EDITOR, User::REL_EDITOR_POSTS],
            ],
            RelationshipTypes::HAS_MANY => [
                self::REL_COMMENTS => [Comment::class, Comment::FIELD_ID_POST, Comment::REL_POST],
            ],
        ];
    }
}
