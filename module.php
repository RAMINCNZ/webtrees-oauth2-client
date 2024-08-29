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

use Composer\Autoload\ClassLoader;

//This webtrees custom module
require __DIR__ . '/OAuth2Client.php';
require __DIR__ . '/AuthorizationProviderFactory.php';
require __DIR__ . '/AuthorizationProviderInterface.php';
require __DIR__ . '/LoginWithAuthorizationProviderAction.php';

//Provider wrappers within the custom module, which implement a webtrees interface to OAuth2 providers
require __DIR__ . '/Provider/JoomlaAuthoriationProvider.php';

$loader = new ClassLoader();

//league/oauth2-client (generic OAuth2 authorization provider, used for Joomla)
$loader->addPsr4('League\\OAuth2\\Client\\', __DIR__ . '/vendor/league/oauth2-client/src');

//More18N translation
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/vendor/cissee/vesta-webtrees-2-custom-modules/vesta_common/patchedWebtrees");
$loader->register();

return new OAuth2Client();
