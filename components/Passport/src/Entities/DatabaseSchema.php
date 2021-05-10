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

declare(strict_types=1);

namespace Limoncello\Passport\Entities;

use Limoncello\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Limoncello\Passport\Models\Client as ClientModel;
use Limoncello\Passport\Models\ClientScope as ClientScopeModel;
use Limoncello\Passport\Models\RedirectUri as RedirectUriModel;
use Limoncello\Passport\Models\Scope as ScopeModel;
use Limoncello\Passport\Models\Token as TokenModel;
use Limoncello\Passport\Models\TokenScope as TokenScopeModel;

/**
 * @package Limoncello\Passport
 *
 * @SuppressWarnings(PHPMD)
 */
class DatabaseSchema implements DatabaseSchemaInterface
{
    /** Table name */
    const TABLE_CLIENTS = ClientModel::TABLE_NAME;

    /** View name */
    const VIEW_CLIENTS = 'vw_oauth_clients';

    /** Table name */
    const TABLE_CLIENTS_SCOPES = ClientScopeModel::TABLE_NAME;

    /** Table name */
    const TABLE_REDIRECT_URIS = RedirectUriModel::TABLE_NAME;

    /** Table name */
    const TABLE_SCOPES = ScopeModel::TABLE_NAME;

    /** Table name */
    const TABLE_TOKENS = TokenModel::TABLE_NAME;

    /** View name */
    const VIEW_TOKENS = 'vw_oauth_tokens';

    /** Table name */
    const TABLE_TOKENS_SCOPES = TokenScopeModel::TABLE_NAME;

    /** View name */
    const VIEW_USERS = 'vw_oauth_users';

    /** Field name */
    const CLIENTS_SCOPES_FIELD_ID = ClientScopeModel::FIELD_ID;

    /** Field name */
    const TOKENS_SCOPES_FIELD_ID = TokenScopeModel::FIELD_ID;

    /** View name */
    const VIEW_PASSPORT = 'vw_oauth_passport';

    /**
     * @var string|null
     */
    private $usersTableName;

    /**
     * @var string|null
     */
    private $usersIdColumn;

    /**
     * @param null|string $usersTableName
     * @param null|string $usersIdColumn
     */
    public function __construct(string $usersTableName = null, string $usersIdColumn = null)
    {
        $this->usersTableName = $usersTableName;
        $this->usersIdColumn  = $usersIdColumn;
    }

    /**
     * @inheritdoc
     */
    public function getClientsTable(): string
    {
        return static::TABLE_CLIENTS;
    }

    /**
     * @inheritdoc
     */
    public function getClientsView(): string
    {
        return static::VIEW_CLIENTS;
    }

    /**
     * @inheritdoc
     */
    public function getClientsViewScopesColumn(): string
    {
        return Client::FIELD_SCOPES;
    }

    /**
     * @inheritdoc
     */
    public function getClientsViewRedirectUrisColumn(): string
    {
        return Client::FIELD_REDIRECT_URIS;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIdentityColumn(): string
    {
        return Client::FIELD_ID;
    }

    /**
     * @inheritDoc
     */
    public function getClientsUuidColumn(): string
    {
        return Client::FIELD_UUID;
    }

    /**
     * @inheritdoc
     */
    public function getClientsNameColumn(): string
    {
        return Client::FIELD_NAME;
    }

    /**
     * @inheritdoc
     */
    public function getClientsDescriptionColumn(): string
    {
        return Client::FIELD_DESCRIPTION;
    }

    /**
     * @inheritdoc
     */
    public function getClientsCredentialsColumn(): string
    {
        return Client::FIELD_CREDENTIALS;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsConfidentialColumn(): string
    {
        return Client::FIELD_IS_CONFIDENTIAL;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsScopeExcessAllowedColumn(): string
    {
        return Client::FIELD_IS_SCOPE_EXCESS_ALLOWED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsUseDefaultScopeColumn(): string
    {
        return Client::FIELD_IS_USE_DEFAULT_SCOPE;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsCodeGrantEnabledColumn(): string
    {
        return Client::FIELD_IS_CODE_GRANT_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsImplicitGrantEnabledColumn(): string
    {
        return Client::FIELD_IS_IMPLICIT_GRANT_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsPasswordGrantEnabledColumn(): string
    {
        return Client::FIELD_IS_PASSWORD_GRANT_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsClientGrantEnabledColumn(): string
    {
        return Client::FIELD_IS_CLIENT_GRANT_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsIsRefreshGrantEnabledColumn(): string
    {
        return Client::FIELD_IS_REFRESH_GRANT_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getClientsCreatedAtColumn(): string
    {
        return Client::FIELD_CREATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getClientsUpdatedAtColumn(): string
    {
        return Client::FIELD_UPDATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getClientsScopesTable(): string
    {
        return static::TABLE_CLIENTS_SCOPES;
    }

    /**
     * @inheritdoc
     */
    public function getClientsScopesIdentityColumn(): string
    {
        return static::CLIENTS_SCOPES_FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getClientsScopesClientIdentityColumn(): string
    {
        return Client::FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getClientsScopesScopeIdentityColumn(): string
    {
        return Scope::FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisTable(): string
    {
        return static::TABLE_REDIRECT_URIS;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisIdentityColumn(): string
    {
        return RedirectUri::FIELD_ID;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrisUuidColumn(): string
    {
        return RedirectUri::FIELD_UUID;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisClientIdentityColumn(): string
    {
        return RedirectUri::FIELD_ID_CLIENT;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisValueColumn(): string
    {
        return RedirectUri::FIELD_VALUE;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisCreatedAtColumn(): string
    {
        return RedirectUri::FIELD_CREATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrisUpdatedAtColumn(): string
    {
        return RedirectUri::FIELD_UPDATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getScopesTable(): string
    {
        return static::TABLE_SCOPES;
    }

    /**
     * @inheritdoc
     */
    public function getScopesIdentityColumn(): string
    {
        return Scope::FIELD_ID;
    }

    /**
     * @inheritDoc
     */
    public function getScopesUuidColumn(): string
    {
        return Scope::FIELD_UUID;
    }

    /**
     * @inheritdoc
     */
    public function getScopesDescriptionColumn(): string
    {
        return Scope::FIELD_DESCRIPTION;
    }

    /**
     * @inheritdoc
     */
    public function getScopesCreatedAtColumn(): string
    {
        return Scope::FIELD_CREATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getScopesUpdatedAtColumn(): string
    {
        return Scope::FIELD_UPDATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getTokensTable(): string
    {
        return static::TABLE_TOKENS;
    }

    /**
     * @inheritdoc
     */
    public function getTokensView(): string
    {
        return static::VIEW_TOKENS;
    }

    /**
     * @inheritdoc
     */
    public function getTokensViewScopesColumn(): string
    {
        return Token::FIELD_SCOPES;
    }

    /**
     * @inheritdoc
     */
    public function getTokensIdentityColumn(): string
    {
        return Token::FIELD_ID;
    }

    /**
     * @inheritDoc
     */
    public function getTokensUuidColumn(): string
    {
        return Token::FIELD_UUID;
    }

    /**
     * @inheritdoc
     */
    public function getTokensIsEnabledColumn(): string
    {
        return Token::FIELD_IS_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function getTokensIsScopeModified(): string
    {
        return Token::FIELD_IS_SCOPE_MODIFIED;
    }

    /**
     * @inheritdoc
     */
    public function getTokensClientIdentityColumn(): string
    {
        return Token::FIELD_ID_CLIENT;
    }

    /**
     * @inheritdoc
     */
    public function getTokensUserIdentityColumn(): string
    {
        return Token::FIELD_ID_USER;
    }

    /**
     * @inheritdoc
     */
    public function getTokensRedirectUriColumn(): string
    {
        return Token::FIELD_REDIRECT_URI;
    }

    /**
     * @inheritdoc
     */
    public function getTokensCodeColumn(): string
    {
        return Token::FIELD_CODE;
    }

    /**
     * @inheritdoc
     */
    public function getTokensValueColumn(): string
    {
        return Token::FIELD_VALUE;
    }

    /**
     * @inheritdoc
     */
    public function getTokensTypeColumn(): string
    {
        return Token::FIELD_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function getTokensRefreshColumn(): string
    {
        return Token::FIELD_REFRESH;
    }

    /**
     * @inheritdoc
     */
    public function getTokensCodeCreatedAtColumn(): string
    {
        return Token::FIELD_CODE_CREATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getTokensValueCreatedAtColumn(): string
    {
        return Token::FIELD_VALUE_CREATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getTokensRefreshCreatedAtColumn(): string
    {
        return Token::FIELD_REFRESH_CREATED_AT;
    }

    /**
     * @inheritDoc
     */
    public function getTokensCreatedAtColumn(): string
    {
        return Token::FIELD_CREATED_AT;
    }

    /**
     * @inheritDoc
     */
    public function getTokensUpdatedAtColumn(): string
    {
        return Token::FIELD_UPDATED_AT;
    }

    /**
     * @inheritdoc
     */
    public function getTokensScopesTable(): string
    {
        return static::TABLE_TOKENS_SCOPES;
    }

    /**
     * @inheritdoc
     */
    public function getTokensScopesIdentityColumn(): string
    {
        return static::TOKENS_SCOPES_FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getTokensScopesTokenIdentityColumn(): string
    {
        return Token::FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getTokensScopesScopeIdentityColumn(): string
    {
        return Scope::FIELD_ID;
    }

    /**
     * @inheritdoc
     */
    public function getUsersView(): ?string
    {
        return static::VIEW_USERS;
    }

    /**
     * @inheritdoc
     */
    public function getUsersTable(): ?string
    {
        return $this->usersTableName;
    }

    /**
     * @inheritdoc
     */
    public function getUsersIdentityColumn(): ?string
    {
        return $this->usersIdColumn;
    }

    /**
     * @inheritdoc
     */
    public function getPassportView(): ?string
    {
        return static::VIEW_PASSPORT;
    }
}
