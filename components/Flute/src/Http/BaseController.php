<?php namespace Limoncello\Flute\Http;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use Interop\Container\ContainerInterface;
use Limoncello\Flute\Contracts\Api\CrudInterface;
use Limoncello\Flute\Contracts\FactoryInterface;
use Limoncello\Flute\Contracts\Http\ControllerInterface;
use Limoncello\Flute\Contracts\I18n\TranslatorInterface;
use Limoncello\Flute\Contracts\Models\ModelSchemesInterface;
use Limoncello\Flute\Contracts\Models\PaginatedDataInterface;
use Limoncello\Flute\Contracts\Schema\JsonSchemesInterface;
use Limoncello\Flute\Contracts\Schema\SchemaInterface;
use Limoncello\Flute\Http\Traits\CreateApiTrait;
use Limoncello\Flute\Http\Traits\CreateResponsesTrait;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Limoncello\Flute
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class BaseController implements ControllerInterface
{
    use CreateApiTrait, CreateResponsesTrait;

    /** API class name */
    const API_CLASS = null;

    /** JSON API Schema class name */
    const SCHEMA_CLASS = null;

    /** URI key used in routing table */
    const ROUTE_KEY_INDEX = null;

    /**
     * @inheritdoc
     */
    public static function index(array $routeParams, ContainerInterface $container, ServerRequestInterface $request)
    {
        /** @var QueryParametersParserInterface $queryParser */
        $queryParser    = $container->get(QueryParametersParserInterface::class);
        $encodingParams = $queryParser->parse($request);

        list ($filters, $sorts, $includes, $paging) =
            static::mapQueryParameters($container, $encodingParams, static::SCHEMA_CLASS);

        $modelData = static::createApi($container)->index($filters, $sorts, $includes, $paging);
        $responses = static::createResponses($container, $request, $encodingParams);
        $response  = $modelData->getPaginatedData()->getData() === null ?
            $responses->getCodeResponse(404) : $responses->getContentResponse($modelData);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public static function create(array $routeParams, ContainerInterface $container, ServerRequestInterface $request)
    {
        list ($index, $attributes, $toMany) = static::parseInputOnCreate($container, $request);

        $api   = self::createApi($container);
        $index = $api->create($index, $attributes, $toMany);
        $data  = $api->read($index);

        $response = static::createResponses($container, $request)->getCreatedResponse($data);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public static function read(array $routeParams, ContainerInterface $container, ServerRequestInterface $request)
    {
        /** @var QueryParametersParserInterface $queryParser */
        $queryParser    = $container->get(QueryParametersParserInterface::class);
        $encodingParams = $queryParser->parse($request);

        list ($filters, , $includes) = static::mapQueryParameters($container, $encodingParams, static::SCHEMA_CLASS);

        $index     = $routeParams[static::ROUTE_KEY_INDEX];
        $modelData = self::createApi($container)->read($index, $filters, $includes);
        $responses = static::createResponses($container, $request, $encodingParams);
        $response  = $modelData->getPaginatedData()->getData() === null ?
            $responses->getCodeResponse(404) : $responses->getContentResponse($modelData);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public static function update(array $routeParams, ContainerInterface $container, ServerRequestInterface $request)
    {
        $index = $routeParams[static::ROUTE_KEY_INDEX];
        list ($attributes, $toMany) = static::parseInputOnUpdate($index, $container, $request);
        $api   = self::createApi($container);

        return self::updateImpl($index, $attributes, $toMany, $container, $request, $api);
    }

    /**
     * @inheritdoc
     */
    public static function delete(array $routeParams, ContainerInterface $container, ServerRequestInterface $request)
    {
        $index = $routeParams[static::ROUTE_KEY_INDEX];
        return static::deleteImpl($index, $container, $request, self::createApi($container));
    }

    /**
     * @param string                 $index
     * @param string                 $relationshipName
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected static function readRelationship(
        $index,
        $relationshipName,
        ContainerInterface $container,
        ServerRequestInterface $request
    ) {
        /** @var PaginatedDataInterface $relData */
        /** @var EncodingParametersInterface $encodingParams */
        list ($relData, $encodingParams) = self::readRelationshipData($index, $relationshipName, $container, $request);

        $responses = static::createResponses($container, $request, $encodingParams);
        $response  = $relData->getData() === null ?
            $responses->getCodeResponse(404) : $responses->getContentResponse($relData);

        return $response;
    }

    /**
     * @param string                 $index
     * @param string                 $relationshipName
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected static function readRelationshipIdentifiers(
        $index,
        $relationshipName,
        ContainerInterface $container,
        ServerRequestInterface $request
    ) {
        /** @var PaginatedDataInterface $relData */
        /** @var EncodingParametersInterface $encodingParams */
        list ($relData, $encodingParams) = self::readRelationshipData($index, $relationshipName, $container, $request);

        $responses = static::createResponses($container, $request, $encodingParams);
        $response  = $relData->getData() === null ?
            $responses->getCodeResponse(404) : $responses->getIdentifiersResponse($relData);

        return $response;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return CrudInterface
     */
    protected static function createApi(ContainerInterface $container)
    {
        return static::createApiByClass($container, static::API_CLASS);
    }

    /**
     * @param ContainerInterface          $container
     * @param EncodingParametersInterface $parameters
     * @param string                      $schemaClass
     *
     * @return array
     */
    protected static function mapQueryParameters(
        ContainerInterface $container,
        EncodingParametersInterface $parameters,
        $schemaClass
    ) {
        /** @var FactoryInterface $factory */
        $factory = $container->get(FactoryInterface::class);
        $errors  = $factory->createErrorCollection();
        $queryTransformer = new QueryTransformer(
            $container->get(ModelSchemesInterface::class),
            $container->get(JsonSchemesInterface::class),
            $container->get(TranslatorInterface::class),
            $schemaClass
        );

        $result = $queryTransformer->mapParameters($errors, $parameters);
        if ($errors->count() > 0) {
            throw new JsonApiException($errors);
        }

        return $result;
    }

    /**
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    protected static function parseJson(ContainerInterface $container, ServerRequestInterface $request)
    {
        $body = (string)$request->getBody();
        if (empty($body) === true || ($json = json_decode($body, true)) === null) {
            /** @var FactoryInterface $factory */
            $factory = $container->get(FactoryInterface::class);
            $errors  = $factory->createErrorCollection();
            /** @var TranslatorInterface $translator */
            $translator = $container->get(TranslatorInterface::class);
            $errors->addDataError($translator->get(TranslatorInterface::MSG_ERR_INVALID_ELEMENT));
            throw new JsonApiException($errors);
        }

        return $json;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SchemaInterface
     */
    protected static function getSchema(ContainerInterface $container)
    {
        /** @var SchemaInterface $schemaClass */
        $schemaClass = static::SCHEMA_CLASS;
        $modelClass  = $schemaClass::MODEL;
        /** @var JsonSchemesInterface $jsonSchemes */
        $jsonSchemes = $container->get(JsonSchemesInterface::class);
        $schema      = $jsonSchemes->getSchemaByType($modelClass);

        return $schema;
    }

    /**
     * @param int|string             $parentIndex
     * @param string                 $relationshipName
     * @param int|string             $childIndex
     * @param string                 $childApiClass
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected static function deleteInRelationship(
        $parentIndex,
        $relationshipName,
        $childIndex,
        $childApiClass,
        ContainerInterface $container,
        ServerRequestInterface $request
    ) {
        /** @var SchemaInterface $schemaClass */
        $schemaClass  = static::SCHEMA_CLASS;
        $modelRelName = $schemaClass::getMappings()[SchemaInterface::SCHEMA_RELATIONSHIPS][$relationshipName];
        $hasChild     = self::createApi($container)->hasInRelationship($parentIndex, $modelRelName, $childIndex);
        if ($hasChild === false) {
            return static::createResponses($container, $request)->getCodeResponse(404);
        }

        $childApi = self::createApiByClass($container, $childApiClass);

        return static::deleteImpl($childIndex, $container, $request, $childApi);
    }

    /** @noinspection PhpTooManyParametersInspection
     * @param int|string             $parentIndex
     * @param string                 $relationshipName
     * @param int|string             $childIndex
     * @param array                  $attributes
     * @param array                  $toMany
     * @param string                 $childApiClass
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected static function updateInRelationship(
        $parentIndex,
        $relationshipName,
        $childIndex,
        array $attributes,
        array $toMany,
        $childApiClass,
        ContainerInterface $container,
        ServerRequestInterface $request
    ) {
        /** @var SchemaInterface $schemaClass */
        $schemaClass  = static::SCHEMA_CLASS;
        $modelRelName = $schemaClass::getMappings()[SchemaInterface::SCHEMA_RELATIONSHIPS][$relationshipName];
        $hasChild     = self::createApi($container)->hasInRelationship($parentIndex, $modelRelName, $childIndex);
        if ($hasChild === false) {
            return static::createResponses($container, $request)->getCodeResponse(404);
        }

        $childApi = self::createApiByClass($container, $childApiClass);

        return static::updateImpl($childIndex, $attributes, $toMany, $container, $request, $childApi);
    }

    /**
     * @param string                 $index
     * @param string                 $relationshipName
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     *
     * @return array [PaginatedDataInterface, EncodingParametersInterface]
     */
    private static function readRelationshipData(
        $index,
        $relationshipName,
        ContainerInterface $container,
        ServerRequestInterface $request
    ) {
        /** @var QueryParametersParserInterface $queryParser */
        $queryParser    = $container->get(QueryParametersParserInterface::class);
        $encodingParams = $queryParser->parse($request);

        /** @var JsonSchemesInterface $jsonSchemes */
        $jsonSchemes  = $container->get(JsonSchemesInterface::class);
        $targetSchema = $jsonSchemes->getRelationshipSchema(static::SCHEMA_CLASS, $relationshipName);
        list ($filters, $sorts, , $paging) =
            static::mapQueryParameters($container, $encodingParams, get_class($targetSchema));

        /** @var SchemaInterface $schemaClass */
        $schemaClass  = static::SCHEMA_CLASS;
        $modelRelName = $schemaClass::getMappings()[SchemaInterface::SCHEMA_RELATIONSHIPS][$relationshipName];
        $relData = self::createApi($container)->readRelationship($index, $modelRelName, $filters, $sorts, $paging);

        return [$relData, $encodingParams];
    }

    /**
     * @param string|int             $index
     * @param array                  $attributes
     * @param array                  $toMany
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     * @param CrudInterface          $api
     *
     * @return ResponseInterface
     */
    private static function updateImpl(
        $index,
        array $attributes,
        array $toMany,
        ContainerInterface $container,
        ServerRequestInterface $request,
        CrudInterface $api
    ) {
        $updated   = $api->update($index, $attributes, $toMany);
        $responses = static::createResponses($container, $request);

        if ($updated <= 0) {
            return $responses->getCodeResponse(404);
        }

        $modelData = $api->read($index);
        $response  = $responses->getContentResponse($modelData);

        return $response;
    }

    /**
     * @param string|int             $index
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     * @param CrudInterface          $api
     *
     * @return ResponseInterface
     */
    private static function deleteImpl(
        $index,
        ContainerInterface $container,
        ServerRequestInterface $request,
        CrudInterface $api
    ) {
        $api->delete($index);
        $response = static::createResponses($container, $request)->getCodeResponse(204);

        return $response;
    }
}