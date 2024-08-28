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

use ReflectionMethod;


/**
 * Factory for an OAuth2 authorization provider with a defined interface for webtrees integration
 */
class AuthorizationProviderFactory
{
    /**
     * Create an OAuth2 authorization provider
     * 
     * @param string                    $name
     * 
     * @return AuthorizationProviderInterface
     */
    public function make(string $name) : ?AuthorizationProviderInterface
    {
        if ($name === 'Joomla') {
            return new JoomlaAuthoriationProvider([
                'clientId'                => 'EyOiaMTefADMiqQMUGzbbwdQYeIxSH',    // The client ID assigned to you by the provider
                'clientSecret'            => 'EYSittQRplBhuzbsCfxCOIxwJZrbwJ',    // The client password assigned to you by the provider
                'redirectUri'             => 'http://nas-synology220/webtrees/index.php?route=/webtrees' . OAuth2Client::REDIRECT_ROUTE,
                'urlAuthorize'            => 'http://nas-synology220/joomla/index.php',
                'urlAccessToken'          => 'http://nas-synology220/joomla/index.php',
                'urlResourceOwnerDetails' => 'http://nas-synology220/joomla/index.php'
            ]);
        }

        return null;
    }

	/**
     * Return the names of all available authorization providers
     *
     * @return array<string>
     */ 

    public static function getAuthorizatonProviderNames(): array {

        $provider_names = [];

        foreach (get_declared_classes() as $class_name) {

            $name_space = str_replace('\\\\', '\\',__NAMESPACE__ ) .'\\';

            if (strpos($class_name, $name_space) !==  false) {
                if (in_array($name_space . 'AuthorizationProviderInterface', class_implements($class_name))) {
                    $reflectionMethod = new ReflectionMethod($class_name, 'getName');
                    $provider_names[] = $reflectionMethod->invoke(null);
                }
            }
        }

        return $provider_names;
    }
}
