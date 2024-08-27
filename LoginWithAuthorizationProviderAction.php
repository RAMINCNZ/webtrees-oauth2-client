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
 * View a modal to change XML export settings.
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
        $code  = Validator::queryParams($request)->string('code', '');
        $state = Validator::queryParams($request)->string('state', '');

        $oauth2_client = $this->module_service->findByName(OAuth2Client::activeModuleName());
        $webtrees_user_data = null;

        $provider_name = 'Joomla';
        $provider = new GenericProvider([
            'clientId'                => 'EyOiaMTefADMiqQMUGzbbwdQYeIxSH',    // The client ID assigned to you by the provider
            'clientSecret'            => 'EYSittQRplBhuzbsCfxCOIxwJZrbwJ',    // The client password assigned to you by the provider
            'redirectUri'             => 'http://nas-synology220/webtrees/index.php?route=/webtrees' . OAuth2Client::REDIRECT_ROUTE,
            'urlAuthorize'            => 'http://nas-synology220/joomla/index.php',
            'urlAccessToken'          => 'http://nas-synology220/joomla/index.php',
            'urlResourceOwnerDetails' => 'http://nas-synology220/joomla/index.php'
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
                'alert_tpye'   => OAuth2Client::ALERT_DANGER, 
                'module_name'  => $oauth2_client->title(),
                'text'         => 'Invalid state in communication with authorization provider.-',
            ]);
        } else {        
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
        
                // Using the access token, we can look up details about the resource owner.
                $resourceOwner = $provider->getResourceOwner($accessToken);
        
                $user_data = $resourceOwner->toArray();

                $webtrees_user_data = new User(
                    $user_data['id'] ?? '',
                    $user_data['name'] ?? '',
                    $user_data['username'] ?? '',
                    $user_data['email'] ?? ''
                );

            } catch (IdentityProviderException $e) {

                // Failed to get the access token or user details.
                return $this->viewResponse(OAuth2Client::viewsNamespace() . '::alert', [
                    'alert_type'   => OAuth2Client::ALERT_DANGER,
                    'module_name'  => $oauth2_client->title(),
                    'text'         => I18N::translate('Failed to get the access token or the user details from the authorization provider.') . ': ' . $e->getMessage(),
            ]);
            }
        }

        //If no user name was retrieved from authorization, redirect to login page
        if (($webtrees_user_data->userName() ?? '') === '') {
            return redirect(route(LoginPage::class));
        }

        $tree         = Validator::attributes($request)->treeOptional();
        $default_url  = route(HomePage::class);
        $username     = $webtrees_user_data->userName() ?? '';
        $url          = Validator::parsedBody($request)->isLocalUrl()->string('url', $default_url);
        $title        = MoreI18N::xlate('Request a new user account');
        $show_caution = Site::getPreference('SHOW_REGISTER_CAUTION') === '1';

        $user = $this->user_service->findByIdentifier($username);

        //If user does not exist already, redirect to registration page based on the remote authorization user data
        if ($user === null) { 
            return $this->viewResponse('::register-page', [
                'captcha'      => $this->captcha_service->createCaptcha(),
                'comments'     => 'Automatic user registration after sign in with authorization provider',
                'email'        => $webtrees_user_data->email(),
                'realname'     => $webtrees_user_data->realName() . ' (' . $provider_name .')',
                'show_caution' => $show_caution,
                'title'        => $title,
                'tree'         => $tree instanceof Tree ? $tree->name() : null,
                'username'     => $webtrees_user_data->userName(),
                'password'     => $accessToken->getToken(),
            ]);
        }            

        //Login
        //Code from webtrees LoginAction.php
        try {
            $this->doLogin($username);

            if (Auth::isAdmin() && $this->upgrade_service->isUpgradeAvailable()) {
                FlashMessages::addMessage(I18N::translate('A new version of webtrees is available.') . ' <a class="alert-link" href="' . e(route(UpgradeWizardPage::class)) . '">' . I18N::translate('Upgrade to webtrees %s.', '<span dir="ltr">' . $this->upgrade_service->latestVersion() . '</span>') . '</a>');
            }

            // Redirect to the target URL
            return redirect($url);
        } catch (Exception $ex) {
            // Failed to log in.
            FlashMessages::addMessage($ex->getMessage(), 'danger');

            return redirect(route(LoginPage::class, [
                'tree'     => $tree instanceof Tree ? $tree->name() : null,
                'username' => $username,
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
            throw new Exception(I18N::translate('You cannot sign in because your browser does not accept cookies.'));
        }

        $user = $this->user_service->findByIdentifier($username);

        if ($user === null) {
            Log::addAuthenticationLog('Login failed (no such user/email): ' . $username);
            throw new Exception(I18N::translate('The username or password is incorrect.'));
        }

        if ($user->getPreference(UserInterface::PREF_IS_EMAIL_VERIFIED) !== '1') {
            Log::addAuthenticationLog('Login failed (not verified by user): ' . $username);
            throw new Exception(I18N::translate('This account has not been verified. Please check your email for a verification message.'));
        }

        if ($user->getPreference(UserInterface::PREF_IS_ACCOUNT_APPROVED) !== '1') {
            Log::addAuthenticationLog('Login failed (not approved by admin): ' . $username);
            throw new Exception(I18N::translate('This account has not been approved. Please wait for an administrator to approve it.'));
        }

        Auth::login($user);
        Log::addAuthenticationLog('Login: ' . Auth::user()->userName() . '/' . Auth::user()->realName());
        Auth::user()->setPreference(UserInterface::PREF_TIMESTAMP_ACTIVE, (string) time());

        Session::put('language', Auth::user()->getPreference(UserInterface::PREF_LANGUAGE));
        Session::put('theme', Auth::user()->getPreference(UserInterface::PREF_THEME));
        I18N::init(Auth::user()->getPreference(UserInterface::PREF_LANGUAGE));
    }
}
