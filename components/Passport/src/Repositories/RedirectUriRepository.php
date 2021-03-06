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

namespace Whoa\Passport\Repositories;

use DateTimeImmutable;
use Doctrine\DBAL\Exception as DBALException;
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;

/**
 * @package Whoa\Passport
 */
abstract class RedirectUriRepository extends BaseRepository implements RedirectUriRepositoryInterface
{
    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function indexClientUris(string $clientIdentifier): array
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $clientIdColumn = $this->getDatabaseSchema()->getRedirectUrisClientIdentityColumn();
            $statement      = $query
                ->select(['*'])
                ->from($this->getTableNameForWriting())
                ->where($clientIdColumn . '=' . $this->createTypedParameter($query, $clientIdentifier))
                ->execute();

            $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
            $result = $statement->fetchAll();

            return $result;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (DBALException $exception) {
            $message = 'Reading client redirect URIs failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function create(RedirectUriInterface $redirectUri): RedirectUriInterface
    {
        try {
            $now    = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->createResource([
                $schema->getRedirectUrisClientIdentityColumn() => $redirectUri->getClientIdentifier(),
                $schema->getRedirectUrisUuidColumn()           => $redirectUri->getUuid(),
                $schema->getRedirectUrisValueColumn()          => $redirectUri->getValue(),
                $schema->getRedirectUrisCreatedAtColumn()      => $now,
            ]);
            $identifier = $this->getLastInsertId();

            $redirectUri->setIdentifier($identifier)->setUuid()->setCreatedAt($now);

            return $redirectUri;
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function read(int $identifier): RedirectUriInterface
    {
        try {
            return $this->readResource($identifier);
        } catch (RepositoryException $exception) {
            $message = 'Reading client redirect URIs failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function update(RedirectUriInterface $redirectUri): void
    {
        try {
            $now    = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->updateResource($redirectUri->getIdentifier(), [
                $schema->getRedirectUrisClientIdentityColumn() => $redirectUri->getClientIdentifier(),
                $schema->getRedirectUrisValueColumn()          => $redirectUri->getValue(),
                $schema->getRedirectUrisUpdatedAtColumn()      => $now,
            ]);
            $redirectUri->setUpdatedAt($now);
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function delete(int $identifier): void
    {
        try {
            $this->deleteResource($identifier);
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForWriting(): string
    {
        return $this->getDatabaseSchema()->getRedirectUrisTable();
    }

    /**
     * @inheritdoc
     */
    protected function getPrimaryKeyName(): string
    {
        return $this->getDatabaseSchema()->getRedirectUrisIdentityColumn();
    }
}
