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

namespace Limoncello\Tests\Flute\Data\Models;

use Doctrine\DBAL\Types\Types;
use Limoncello\Contracts\Data\RelationshipTypes;
use Limoncello\Tests\Flute\Data\Types\SystemDateTimeType;

/**
 * @package Limoncello\Tests\Flute
 */
class Category extends Model
{
    /** @inheritdoc */
    const TABLE_NAME = 'categories';

    /** @inheritdoc */
    const FIELD_ID = 'id_category';

    /** Field name */
    const FIELD_ID_PARENT = 'id_parent_fk';

    /** Relationship name */
    const REL_PARENT = 'parent';

    /** Relationship name */
    const REL_CHILDREN = 'children';

    /** Field name */
    const FIELD_NAME = 'name';

    /** Length constant */
    const LENGTH_NAME = 255;

    /**
     * @inheritdoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID         => Types::INTEGER,
            self::FIELD_ID_PARENT  => Types::INTEGER,
            self::FIELD_NAME       => Types::STRING,
            self::FIELD_CREATED_AT => SystemDateTimeType::NAME,
            self::FIELD_UPDATED_AT => SystemDateTimeType::NAME,
            self::FIELD_DELETED_AT => SystemDateTimeType::NAME,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_NAME => self::LENGTH_NAME,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO => [
                self::REL_PARENT => [Category::class, self::FIELD_ID_PARENT, Category::REL_CHILDREN],
            ],
            RelationshipTypes::HAS_MANY   => [
                self::REL_CHILDREN => [Category::class, self::FIELD_ID_PARENT, Category::REL_PARENT],
            ],
        ];
    }
}
