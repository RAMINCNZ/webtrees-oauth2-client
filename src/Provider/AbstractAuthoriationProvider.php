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
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * An abstract OAuth2 authorization client, which provides basic methods
 */
abstract class AbstractAuthoriationProvider
{
    //The authorization provider
    protected AbstractProvider $provider;

    protected const USER_DATA_PRIMARY_KEY   = 'primary_key';
    protected const USER_DATA_MANDATORY_KEY = 'mandatory_key';
    protected const USER_DATA_OPTIONAL_KEY  = 'optional_key';
    protected const USER_DATA_UNUSED_KEY    = 'unused_key';

    /**
     * Get the authorization URL
     *
     * @param  array $options
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options = [])
    {
        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Get the current value of the state parameter
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState()
    {
        return $this->provider->getState();
    }    

    /**
     * Get an access token from the provider using a specified grant and option set.
     *
     * @param  mixed                $grant
     * @param  array<string, mixed> $options
     * @throws IdentityProviderException
     * @return AccessTokenInterface
     */
    public function getAccessToken($grant, array $options = [])
    {
        return $this->provider->getAccessToken($grant, $options);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner(AccessToken $token) : ResourceOwnerInterface
    {
        return $this->provider->getResourceOwner($token);
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
            (int) $resourceOwner->getId() ?? '',
            $user_data['username']        ?? '', //Default has to be empty, because empty username needs to be detected as error
            $user_data['name']            ?? $user_data['username'] ?? '',
            $user_data['email']           ?? '', //Default has to be empty, because empty email needs to be detected as error
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
            'redirectUri',
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
                'user_name' => static::USER_DATA_PRIMARY_KEY,
                'real_name' => static::USER_DATA_OPTIONAL_KEY,
                'email'     => static::USER_DATA_MANDATORY_KEY,
        ];
    } 

    /**
     * Validate the provider
     *
     * @return string   Validation error; empty string if no error
     */
    public function validate() : string
    {
        if (    self::getUserKeyInformation()['user_name'] !== AbstractAuthoriationProvider::USER_DATA_PRIMARY_KEY
            &&  self::getUserKeyInformation()['email']     !== AbstractAuthoriationProvider::USER_DATA_PRIMARY_KEY) {

            return I18N::translate('Cannot use the login data of the authorization provider. Neither username nor email is a primary key.');
        }

        return '';
    }    
}
