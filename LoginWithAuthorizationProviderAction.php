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

use Exception;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Http\RequestHandlers\HomePage;
use Fisharebest\Webtrees\Http\RequestHandlers\LoginPage;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\RequestHandlers\UpgradeWizardPage;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Services\CaptchaService;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\UpgradeService;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Validator;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Perform a login with an authorization provider
 */
class LoginWithAuthorizationProviderAction implements RequestHandlerInterface
{
    use ViewResponseTrait;

	//Module service to search and find modules
	private ModuleService $module_service;

    private UpgradeService $upgrade_service;

    private UserService $user_service;
    private CaptchaService $captcha_service;

    /**
     * @param UpgradeService $upgrade_service
     * @param UserService    $user_service
     */
    public function __construct(UpgradeService $upgrade_service, UserService $user_service, ModuleService $module_service, CaptchaService $captcha_service)
    {
        $this->upgrade_service = $upgrade_service;
        $this->user_service    = $user_service;
        $this->module_service  = $module_service;
        $this->captcha_service = $captcha_service;        
    }

    /**
     * Perform a login.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $code          = Validator::queryParams($request)->string('code', '');
        $state         = Validator::queryParams($request)->string('state', '');
        $provider_name = Validator::queryParams($request)->string('provider_name', '');

        //Save or load the provider name to the session
        if ($provider_name !== '') {
            Session::put(OAuth2Client::activeModuleName() . 'provider_name', $provider_name);
        }
        else {
            $provider_name = Session::get(OAuth2Client::activeModuleName() . 'provider_name');
        }

        $oauth2_client = $this->module_service->findByName(OAuth2Client::activeModuleName());
        $user = null;

        $provider = (new AuthorizationProviderFactory())->make($provider_name);

        if ($provider === null) return $this->viewResponse(OAuth2Client::viewsNamespace() . '::alert', [
            'alert_type'   => OAuth2Client::ALERT_DANGER, 
            'module_name'  => $oauth2_client->title(),
            'text'         => I18N::translate('The requested authorization provider could not be found') . ': ' . $provider_name,
        ]);
        
        // If we don't have an authorization code then get one
        if ($code === '') {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            Session::put(OAuth2Client::activeModuleName() . 'oauth2state', $provider->getState());
        
            // Redirect the user to the authorization URL.
            return redirect($authorizationUrl);
        
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif ($state === '' ||  !Session::has(OAuth2Client::activeModuleName() . 'oauth2state') || $state !== Session::get(OAuth2Client::activeModuleName() . 'oauth2state', '')) {
        
            if (Session::get(OAuth2Client::activeModuleName() . 'oauth2state', '') !== '') {
                Session::forget(OAuth2Client::activeModuleName() . 'oauth2state');
            }
        
            return $this->viewResponse(OAuth2Client::viewsNamespace() . '::alert', [
                'alert_type'   => OAuth2Client::ALERT_DANGER, 
                'module_name'  => $oauth2_client->title(),
                'text'         => I18N::translate('Invalid state in communication with authorization provider.'),
            ]);
        } else {        
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
        
                // Using the access token, we can get the user data of the resource owner        
                $user = $provider->getUserData($accessToken);

            } catch (IdentityProviderException $e) {

                // Failed to get the access token or user details.
                return $this->viewResponse(OAuth2Client::viewsNamespace() . '::alert', [
                    'alert_type'   => OAuth2Client::ALERT_DANGER,
                    'module_name'  => $oauth2_client->title(),
                    'text'         => I18N::translate('Failed to get the access token or the user details from the authorization provider.') . ': ' . $e->getMessage(),
            ]);
            }
        }

        //If no user name was or email was retrieved from authorization provider, redirect to login page
        if ($user->userName() === '' OR $user->email() === '') {
            FlashMessages::addMessage(I18N::translate('No valid user account data received from authorizaton provider. User name or email missing.'), 'danger');
            return redirect(route(LoginPage::class));
        }

        $tree         = Validator::attributes($request)->treeOptional();
        $default_url  = route(HomePage::class);
        $url          = Validator::parsedBody($request)->isLocalUrl()->string('url', $default_url);
        $title        = MoreI18N::xlate('Request a new user account');
        $show_caution = Site::getPreference('SHOW_REGISTER_CAUTION') === '1';

        //We add the provider name as a postfix to the user name. This might be helpful to separate namespaces
        //and for future use cases, where we might want to know how users are related to an authorization providers.
        $username     = $user->userName() . '(' . $provider_name .')';

        //If no real name was received from authorization provider, the user name is chosen as default
        $realname     = $user->realName() !== '' ? $user->realName() : $username;

        //If user does not exist already, redirect to registration page based on the authorization provider user data
        if ($this->user_service->findByIdentifier($username) === null) { 
            return $this->viewResponse(OAuth2Client::viewsNamespace() . '::register-page', [
                'captcha'       => $this->captcha_service->createCaptcha(),
                'comments'      => I18N::translate('Automatic user registration after sign in with authorization provider'),
                'email'         => $user->email(),
                'realname'      => $realname,
                'show_caution'  => $show_caution,
                'title'         => $title,
                'tree'          => $tree instanceof Tree ? $tree->name() : null,
                'username'      => $username,
                'password'      => $accessToken->getToken(),
                'provider_name' => $provider_name,
            ]);
        }            

        //Login
        //Code from webtrees LoginAction.php
        try {
            $this->doLogin($username);

            if (Auth::isAdmin() && $this->upgrade_service->isUpgradeAvailable()) {
                FlashMessages::addMessage(MoreI18N::xlate('A new version of webtrees is available.') . ' <a class="alert-link" href="' . e(route(UpgradeWizardPage::class)) . '">' . MoreI18N::xlate('Upgrade to webtrees %s.', '<span dir="ltr">' . $this->upgrade_service->latestVersion() . '</span>') . '</a>');
            }

            // Redirect to the target URL
            return redirect($url);
        } catch (Exception $ex) {
            // Failed to log in.
            FlashMessages::addMessage($ex->getMessage(), 'danger');

            return redirect(route(LoginPage::class, [
                'tree'     => $tree instanceof Tree ? $tree->name() : null,
                'url'      => $url,
            ]));
        }        
    }	

    /**
     * Log in, if we can.  Throw an exception, if we can't.
     *
     * @param string $username
     *
     * @return void
     * @throws Exception
     */
    private function doLogin(string $username): void
    {
        if ($_COOKIE === []) {
            Log::addAuthenticationLog('Login failed (no session cookies): ' . $username);
            throw new Exception(MoreI18N::xlate('You cannot sign in because your browser does not accept cookies.'));
        }

        $user = $this->user_service->findByIdentifier($username);

        if ($user === null) {
            Log::addAuthenticationLog('Login failed (no such user/email): ' . $username);
            throw new Exception(MoreI18N::xlate('The username or password is incorrect.'));
        }

        if ($user->getPreference(UserInterface::PREF_IS_EMAIL_VERIFIED) !== '1') {
            Log::addAuthenticationLog('Login failed (not verified by user): ' . $username);
            throw new Exception(MoreI18N::xlate('This account has not been verified. Please check your email for a verification message.'));
        }

        if ($user->getPreference(UserInterface::PREF_IS_ACCOUNT_APPROVED) !== '1') {
            Log::addAuthenticationLog('Login failed (not approved by admin): ' . $username);
            throw new Exception(MoreI18N::xlate('This account has not been approved. Please wait for an administrator to approve it.'));
        }

        Auth::login($user);
        Log::addAuthenticationLog('Login: ' . Auth::user()->userName() . '/' . Auth::user()->realName());
        Auth::user()->setPreference(UserInterface::PREF_TIMESTAMP_ACTIVE, (string) time());

        Session::put('language', Auth::user()->getPreference(UserInterface::PREF_LANGUAGE));
        Session::put('theme', Auth::user()->getPreference(UserInterface::PREF_THEME));
        I18N::init(Auth::user()->getPreference(UserInterface::PREF_LANGUAGE));
    }
}
