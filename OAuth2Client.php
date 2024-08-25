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

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function substr;


class OAuth2Client extends AbstractModule implements
	ModuleCustomInterface, 
	ModuleConfigInterface,
    RequestHandlerInterface
{
    use ModuleCustomTrait;
    use ModuleConfigTrait;

    //State of the OAuth2 session
    private $oauth2state;

	//Custom module version
	public const CUSTOM_VERSION = '0.0.1';

    //Routes
	protected const REDIRECT_ROUTE = '/OAuth2Client';

	//Github repository
	public const GITHUB_REPO = 'Jefferson49/OAuth2-Client';

	//Github API URL to get the information about the latest releases
	public const GITHUB_API_LATEST_VERSION = 'https://api.github.com/repos/'. self::GITHUB_REPO . '/releases/latest';
	public const GITHUB_API_TAG_NAME_PREFIX = '"tag_name":"v';

	//Author of custom module
	public const CUSTOM_AUTHOR = 'Markus Hemprich';

    //Prefences, Settings
	public const PREF_MODULE_VERSION = 'module_version';

	//Alert tpyes
	public const ALERT_DANGER = 'alert_danger';
	public const ALERT_SUCCESS = 'alert_success';


   /**
     * OAuth2Client constructor.
     */
    public function __construct()
    {
    }

    /**
     * Initialization.
     *
     * @return void
     */
    public function boot(): void
    {
        //Check update of module version
        $this->checkModuleVersionUpdate();

		// Register a namespace for the views.
		View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        //Register a route for remote requests
        $router = Registry::routeFactory()->routeMap();                 
        $router
        ->get(static::class, self::REDIRECT_ROUTE, $this)
        ->allows(RequestMethodInterface::METHOD_POST);     
    }
	
    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return I18N::translate('OAuth2 Client');
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        /* I18N: Description of the “AncestorsChart” module */
        return I18N::translate('A custom module to implement a OAuth2 client for webtrees.');
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * Get the active module name, e.g. the name of the currently running module
     *
     * @return string
     */
    public static function activeModuleName(): string
    {
        return '_' . basename(__DIR__) . '_';
    }
    
    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName(): string
    {
        return self::CUSTOM_AUTHOR;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleLatestVersion()
     */
    public function customModuleLatestVersion(): string
    {
        // No update URL provided.
        if (self::GITHUB_API_LATEST_VERSION === '') {
            return $this->customModuleVersion();
        }
        return Registry::cache()->file()->remember(
            $this->name() . '-latest-version',
            function (): string {
                try {
                    $client = new Client(
                        [
                        'timeout' => 3,
                        ]
                    );

                    $response = $client->get(self::GITHUB_API_LATEST_VERSION);

                    if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
                        $content = $response->getBody()->getContents();
                        preg_match_all('/' . self::GITHUB_API_TAG_NAME_PREFIX . '\d+\.\d+\.\d+/', $content, $matches, PREG_OFFSET_CAPTURE);

						if(!empty($matches[0]))
						{
							$version = $matches[0][0][0];
							$version = substr($version, strlen(self::GITHUB_API_TAG_NAME_PREFIX));	
						}
						else
						{
							$version = $this->customModuleVersion();
						}

                        return $version;
                    }
                } catch (GuzzleException $ex) {
                    // Can't connect to the server?
                }

                return $this->customModuleVersion();
            },
            86400
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     */
    public function customModuleSupportUrl(): string
    {
        return 'https://github.com/' . self::GITHUB_REPO;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $language
     *
     * @return array
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customTranslations()
     */
    public function customTranslations(string $language): array
    {
        $lang_dir   = $this->resourcesFolder() . 'lang/';
        $file       = $lang_dir . $language . '.mo';
        if (file_exists($file)) {
            return (new Translation($file))->asArray();
        } else {
            return [];
        }
    }

    /**
     * View module settings in control panel
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';       

        return $this->viewResponse(
            $this->name() . '::settings',
            [
                'title'                               => $this->title(),
            ]
        );
    }

    /**
     * Save module settings after returning from control panel
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $save                       = Validator::parsedBody($request)->string('save', '');

        //Save settings

        //Finally, show a success message
        $message = I18N::translate('The preferences for the module "%s" were updated.', $this->title());
        FlashMessages::addMessage($message, 'success');	

        return redirect($this->getConfigLink());
    }

    /**
     * Check if module version is new and start update activities if needed
     *
     * @return void
     */
    public function checkModuleVersionUpdate(): void
    {
        $updated = false;
       
        //Update custom module version if changed
        if($this->getPreference(self::PREF_MODULE_VERSION, '') !== self::CUSTOM_VERSION) {
            $this->setPreference(self::PREF_MODULE_VERSION, self::CUSTOM_VERSION);
            $updated = true;
        }

        if ($updated) {
            //Show flash message for update of preferences
            $message = I18N::translate('The preferences for the custom module "%s" were sucessfully updated to the new module version %s.', $this->title(), self::CUSTOM_VERSION);
            FlashMessages::addMessage($message, 'success');	
        }        
    }

    /**
     * Handle OAuth2 access with generic provider
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */	
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $code  = Validator::queryParams($request)->string('code', '');
        $state = Validator::queryParams($request)->string('state', '');

        $provider = new GenericProvider([
            'clientId'                => 'EyOiaMTefADMiqQMUGzbbwdQYeIxSH',    // The client ID assigned to you by the provider
            'clientSecret'            => 'EYSittQRplBhuzbsCfxCOIxwJZrbwJ',    // The client password assigned to you by the provider
            'redirectUri'             => 'http://nas-synology220/webtrees/index.php?route=/webtrees' . self::REDIRECT_ROUTE,
            'urlAuthorize'            => 'http://nas-synology220/joomla/index.php',
            'urlAccessToken'          => 'http://nas-synology220/joomla/index.php',
            'urlResourceOwnerDetails' => 'http://nas-synology220/joomla/index.php'
        ]);
        
        // If we don't have an authorization code then get one
        if ($code === '') {
        
            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            Session::put($this->name() . 'oauth2state', $provider->getState());
        
            // Redirect the user to the authorization URL.
            return redirect($authorizationUrl);
        
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif ($state === '' ||  !Session::has($this->name() . 'oauth2state') || $state !== Session::get($this->name() . 'oauth2state', '')) {
        
            if (Session::get($this->name() . 'oauth2state', '') !== '') {
                Session::forget($this->name() . 'oauth2state');
            }
        
            return $this->viewResponse(
                $this->name() . '::alert',
                [
                    'alert_tpye'   => self::ALERT_DANGER,
                    'module_name'  => $this->name(),
                    'text'         => 'Invalid state',
                ]
            );

        } else {
        
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
        
                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                /*
                echo 'Access Token: ' . $accessToken->getToken() . "<br>";
                echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
                echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
                echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";
                */
        
                // Using the access token, we may look up details about the
                // resource owner.
                $resourceOwner = $provider->getResourceOwner($accessToken);
        
                $user_data = $resourceOwner->toArray();

                $this->layout = 'layouts/administration';
                return $this->viewResponse(
                    $this->name() . '::user_data',
                    [
                        'title'    => 'User Data',
                        'id'       => $user_data['id'] ?? '',
                        'name'     => $user_data['name'] ?? '',
                        'username' => $user_data['username'] ?? '',
                        'email'    => $user_data['email'] ?? '',
                    ]
                );                
        
                // The provider provides a way to get an authenticated API request for
                // the service, using the access token; it returns an object conforming
                // to Psr\Http\Message\RequestInterface.
                /*
                $request = $provider->getAuthenticatedRequest(
                    'GET',
                    'https://service.example.com/resource',
                    $accessToken
                );
                */
        
            } catch (IdentityProviderException $e) {
        
                // Failed to get the access token or user details.
                return $this->viewResponse(
                    $this->name() . '::alert',
                    [
                        'alert_type'   => self::ALERT_DANGER,
                        'module_name'  => $this->name(),
                        'text'         => $e->getMessage(),
                    ]
                );
            }
        }
    }
}