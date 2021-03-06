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

namespace Whoa\Tests\Events\Data\Subscribers;

use Whoa\Events\Contracts\EventHandlerInterface;
use Whoa\Tests\Events\Data\Events\BaseUserEvent;
use Whoa\Tests\Events\Data\Events\UserCreatedEvent;
use Whoa\Tests\Events\Data\Events\UserEvent;
use Whoa\Tests\Events\Data\Events\UserUpdatedEvent;
use function assert;

/**
 * @package Whoa\Tests\Events
 */
class UserSubscribers implements EventHandlerInterface
{
    /**
     * @var bool
     */
    private static $onUser = false;

    /**
     * @var bool
     */
    private static $onBaseUser = false;

    /**
     * @var bool
     */
    private static $onUserCreated = false;

    /**
     * @var bool
     */
    private static $onUserUpdated = false;

    /**
     * @return void
     */
    public static function reset()
    {
        static::$onUser        = false;
        static::$onBaseUser    = false;
        static::$onUserCreated = false;
        static::$onUserUpdated = false;
    }

    /**
     * @param UserEvent $event
     */
    public static function onUser(UserEvent $event)
    {
        assert($event);

        static::$onUser = true;
    }

    /**
     * @return bool
     */
    public static function isOnUser()
    {
        return self::$onUser;
    }

    /**
     * @param BaseUserEvent $event
     */
    public static function onBaseUser(BaseUserEvent $event)
    {
        assert($event);

        static::$onBaseUser = true;
    }

    /**
     * @return bool
     */
    public static function isOnBaseUser(): bool
    {
        return self::$onBaseUser;
    }

    /**
     * @param UserCreatedEvent $event
     */
    public static function onUserCreated(UserCreatedEvent $event)
    {
        assert($event);

        static::$onUserCreated = true;
    }

    /**
     * @return bool
     */
    public static function isOnUserCreated(): bool
    {
        return self::$onUserCreated;
    }

    /**
     * @param UserUpdatedEvent $event
     */
    public static function onUserUpdated(UserUpdatedEvent $event)
    {
        assert($event);

        static::$onUserUpdated = true;
    }

    /**
     * @return bool
     */
    public static function isOnUserUpdated(): bool
    {
        return self::$onUserUpdated;
    }
}
