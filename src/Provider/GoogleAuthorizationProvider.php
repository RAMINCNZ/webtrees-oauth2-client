<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2024 webtrees development team
 *                    <http://webtrees.net>
 *
 * OAuth2Client (webtrees custom module):
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

namespace Jefferson49\Webtrees\Module\OAuth2Client\Provider;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\User;
use Jefferson49\Webtrees\Module\OAuth2Client\Contracts\AuthorizationProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use Exception;


/**
 * An OAuth2 authorization client for Github
 */
class GoogleAuthorizationProvider extends AbstractAuthorizationProvider implements AuthorizationProviderInterface
{        
    //The authorization provider
    protected AbstractProvider $provider;


    /**
     * @param string $redirectUri
     * @param array  $options
     * @param array  $collaborators
     */
    public function __construct(string $redirectUri, array $options = [], array $collaborators = [])
    {
        if ($redirectUri === '' && $options === []) return;

        $options = array_merge($options, [
            'redirectUri'             => $redirectUri,
        ]);

        $this->provider = new Google($options, $collaborators);
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

        try {
            $user_id = (int) $resourceOwner->getId() ?? '';
        }
        catch (Exception $e) {
            throw new IdentityProviderException(I18N::translate('Invalid user data received from the authorization provider') . ': '. json_encode($user_data) . ' . ' . I18N::translate('Check the setting for urlResourceOwnerDetails in the webtrees configuration.'), 0, $user_data);
        }

        return new User(            
            $user_id,

            //User name: Empty, because not provided by Google
            '', 
            
            //Real name:
            $resourceOwner->getName()     ?? '', 
            
            //Email: Default has to be empty, because empty email needs to be detected as error
            $resourceOwner->getEmail()    ?? '',                         
        );
    }      

    /**
     * Returns a list with options that can be passed to the provider
     *
     * @return array   An array of option names, which can be set for this provider.
     *                 Options include `clientId`, `clientSecret`, `redirectUri`, etc.
     */
    public static function getRequiredOptions() : array {
        return [
            'clientId',
            'clientSecret',
        ];
    }

    /**
     * Returns an array with the webtrees user data keys, which defines if they are primary or mandatory
     * See class: Fisharebest\Webtrees\User
     *
     * @return array   An array with the webtrees user data keys. The value for a key defines if it is primary, mandatory, or optional
     */
    public static function getUserKeyInformation() : array {
        return [
                'user_name' => self::USER_DATA_OPTIONAL_KEY,
                'real_name' => self::USER_DATA_OPTIONAL_KEY,
                'email'     => self::USER_DATA_PRIMARY_KEY,
        ];
    }    
}
