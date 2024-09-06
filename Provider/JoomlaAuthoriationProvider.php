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

use Fisharebest\Webtrees\User;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * An OAuth2 authorization client for Joomla
 */
class JoomlaAuthoriationProvider extends AbstractAuthoriationProvider implements AuthorizationProviderInterface
{
    //The authorization provider
    protected AbstractProvider $provider;


    /**
     * @param string $base_url
     * @param array  $options
     * @param array  $collaborators
     */
    public function __construct(string $base_url, array $options = [], array $collaborators = [])
    {
        $options = array_merge($options, [
            'redirectUri'             => OAuth2Client::getRedirectUrl($base_url),

            //The URLs for access token and ressource owner detail are identical to the authorize URL
            'urlAccessToken'          => $options['urlAuthorize'] ?? '',
            'urlResourceOwnerDetails' => $options['urlAuthorize'] ?? '',
        ]);
        
        $this->provider = new GenericProvider($options, $collaborators);
    }

    /**
     * Get the name of the authorization client
     * 
     * @return string
     */
    public static function getName() : string {
        return 'Joomla';
    }

    /**
     * Use access token to get user data from provider and return it as a webtrees User object
     * 
     * @param AccessToken $token
     * 
     * @return User
     */
    public function getUserData(AccessToken $token) : User {

        $resourceOwner = $this->provider->getResourceOwner($token);
        $user_data = $resourceOwner->toArray();

        return new User(
            $user_data['id'] ?? '',
            $user_data['username'] ?? '',
            $user_data['name'] ?? '',
            $user_data['email'] ?? ''
        );
    }

    /**
     * Returns a list with options that can be passed to the provider
     *
     * @return array   An array of option names, which can be set for this provider.
     *                 Options include `clientId`, `clientSecret`, `redirectUri`, etc.
     */
    public static function getOptionNames() : array {
        return [
            'clientId',
            'clientSecret',
            'urlAuthorize',
        ];
    }    
}
