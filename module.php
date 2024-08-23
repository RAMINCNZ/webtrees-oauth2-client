<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2024 webtrees development team
 *                    <http://webtrees.net>
 *
 * Fancy Research Links (webtrees custom module):
 * Copyright (C) 2022 Carmen Just
 *                    <https://justcarmen.nl>
 *
 * ExtendedImportExport (webtrees custom module):
 * Copyright (C) 2024 Markus Hemprich
 *                    <http://www.familienforschung-hemprich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * 
 * OAuth2-Client
 *
 * A weebtrees(https://webtrees.net) 2.1 custom module to implement an OAuth2 client
 * 
 */
 

declare(strict_types=1);

namespace Jefferson49\Webtrees\Module\OAuth2Client;

require __DIR__ . '/OAuth2Client.php';

//league/oauth2-client
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/ArrayAccessorTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/BearerAuthorizationTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/GuardedPropertyTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/MacAuthorizationTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/ProviderRedirectTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/QueryBuilderTrait.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/RequestFactory.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Tool/RequiredParameterTrait.php';

require __DIR__ . '/vendor/league/oauth2-client/src/Grant/Exception/InvalidGrantException.php';

require __DIR__ . '/vendor/league/oauth2-client/src/Grant/AbstractGrant.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Grant/AuthorizationCode.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Grant/ClientCredentials.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Grant/GrantFactory.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Grant/Password.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Grant/RefreshToken.php';

require __DIR__ . '/vendor/league/oauth2-client/src/OptionProvider/OptionProviderInterface.php';
require __DIR__ . '/vendor/league/oauth2-client/src/OptionProvider/PostAuthOptionProvider.php';
require __DIR__ . '/vendor/league/oauth2-client/src/OptionProvider/HttpBasicAuthOptionProvider.php';

require __DIR__ . '/vendor/league/oauth2-client/src/Provider/Exception/IdentityProviderException.php';

require __DIR__ . '/vendor/league/oauth2-client/src/Provider/ResourceOwnerInterface.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Provider/AbstractProvider.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Provider/GenericProvider.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Provider/GenericResourceOwner.php';

require __DIR__ . '/vendor/league/oauth2-client/src/Token/AccessTokenInterface.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Token/ResourceOwnerAccessTokenInterface.php';
require __DIR__ . '/vendor/league/oauth2-client/src/Token/AccessToken.php';


return new OAuth2Client();
