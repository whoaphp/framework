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

namespace Whoa\Tests\Testing;

use Whoa\Testing\Sapi;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

/**
 * @package Whoa\Tests\Testing
 */
class SapiTest extends TestCase
{
    /**
     * Test handle response.
     */
    public function testHandleResponse(): void
    {
        /** @var EmitterInterface $emitter */
        $emitter = Mockery::mock(EmitterInterface::class);
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        $sapi = new Sapi($emitter);
        $sapi->handleResponse($response);
        $this->assertSame($response, $sapi->getResponse());
    }
}
