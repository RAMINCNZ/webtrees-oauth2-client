[![Latest Release](https://img.shields.io/github/v/release/Jefferson49/webtrees-oauth2-client?display_name=tag)](https://github.com/Jefferson49/ExtendedImportExport/releases/latest)
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.1.x-green)](https://webtrees.net/download)

# OAuth 2.0 Client for webtrees
A [webtrees](https://webtrees.net) 2.1 custom module to provide [OAuth 2.0](https://en.wikipedia.org/wiki/OAuth) single sign on ([SSO](https://en.wikipedia.org/wiki/Single_sign-on)) with OAuth 2.0 authorization providers.

##  Table of contents
This README file contains the following main sections:
+   [What are the benefits of this module?](#what-are-the-benefits-of-this-module)
+   [IMPORTANT SECURITY NOTES](#important-security-notes)
+   [Installation](#installation)
+   [Supported Authorization Providers](#supported-authorization-providers)
+   [Configuration of Authorization Providers](#configuration-of-authorization-providers)
    + [General Configuration](#general-configuration)
    + [Generic](#generic)
    + [Github](#github)
    + [Google](#google)
    + [Joomla](#joomla)
+   [Webtrees Version](#webtrees-version)
+   [Translation](#translation)
+   [Bugs and Feature Requests](#bugs-and-feature-requests)
+   [Github Repository](#github-repository)

## What are the benefits of this module?
+ The module provides single sign on ([SSO](https://en.wikipedia.org/wiki/Single_sign-on)) into the webtrees application based on the [OAuth 2.0](https://en.wikipedia.org/wiki/OAuth) standard.
+ A pre-configured set of authorization providers can be selected during webtrees login.
+ If using sign on with an authorization provider, the user account data (i.e. name, user name, email) from the authorization provider is used in webtrees.

## IMPORTANT SECURITY NOTES
It is **highly recommended to use** the **HTTPS** protocol for your webtrees installations. The [HTTPS](https://en.wikipedia.org/wiki/HTTPS) protocol will ensure the encryption of the communication between webtrees and the authorization provider for a secure exchange of secret IDs and secret access tokens.

Please check whether your **webtrees BASE_URL** in the config.ini.php file **starts with "https"**, e.g. https://my_site.net/webtrees.

## Installation
+ Download the [latest release](https://github.com/Jefferson49/webtrees-oauth2-client/releases/latest) of the module
+ Copy the folder "oauth2_client" into the "module_v4" folder of your webtrees installation
+ Check if the module is activated in the control panel:
  + Login to webtrees as an administrator
	+ Go to "Control Panel/All Modules", and find the module called "ExtendedImportExport"
	+ Check if it has a tick for "Enabled"

## Supported Authorization Providers
Currently, the following authorization providers are supported:
+ Generic (can be configured for several authorization providers)
+ Github
+ Google
+ Joomla (with a specific authorization provider installed in Joomla)

## Configuration of Authorization Providers
### General Configuration
In general, the following steps need to be taken to configure an authorization provider:
+ Login on the provider web page
+ Register an OAuth2 app (or web app)
+ Provide the **redirect URL** (or callback URL) for the registered app
+ Get the **client ID** for the registered app
+ Get (or generate) the **client secret** for the registered app
+ Enter the client ID and client secret into your webtrees configuratin file (config.ini.php)

Use the following value as redirect or callback URL with BASE_URL from your webtrees config.ini.php (base_url="xxx"):
```
BASE_URL/index.php?route=/webtrees/OAuth2Client
```

In the following, the configuration is described for a subset of authorization providers. Simular configuration procedures apply to other providers. 

### Generic
+ Configure the OAuth 2.0 server, which shall be used as authorization provider
+ Configure the "Redirect URL" to webtrees within the OAuth 2.0 server:
    + URL: **BASE_URL/index.php?route=/webtrees/OAuth2Client** (BASE_URL from webtrees config.ini.php; base_url='...')
+ Create and check the "Client ID", and "Client Secret" within the OAuth 2.0 server
+ Open your webtrees config.ini.php file and insert the following lines:
```PHP
Generic_clientId='xxx'
Joomla_clientSecret='xxx'
Generic_urlAuthorize='xxx'
Generic_urlAccessToken='xxx'
Generic_urlResourceOwnerDetails='xxx'
Generic_loginButtonLabel='xxx'
```
+ Insert the configuration details from the OAuth 2.0 Server into the newly included configuration lines of your config.ini.php file:
    + **Generic_clientId**='...' (value from the OAuth 2.0 Server)
    + **Generic_clientSecret**='...' (value from the OAuth 2.0 Server)
    + **Generic_urlAuthorize**='...' (value from the OAuth 2.0 Server)
    + **Generic_urlAccessToken**='...' (value from the OAuth 2.0 Server)
    + **Generic_urlResourceOwnerDetails**='...' (value from the OAuth 2.0 Server)
    + **Generic_loginButtonLabel**='...' (the label, which shall be shown for the login button etc.))

### Github
+ Open the [Github](https://github.com/) page and log into your Github account
+ Click on the symbol for your user account on the top right side of the browser
+ Choose "Settings" from the profile menu
+ Choose "Developer settings" on the bottom left side
+ Choose "OAuth2 Apps"
+ Click the button "New OAuth2 App" on the right side
+ Enter the data for the GitHub App:
    + Application name: Can be freely chosen, e.g. "webtrees - Miller family" 
    + Homepage URL: **BASE_URL** (from webtress config.ini.php)
    + Authorization callback URL: **BASE_URL/index.php?route=/webtrees/OAuth2Client** (BASE_URL from webtrees config.ini.php; base_url='...')
+ Press button "Register application"
+ Press button "Create a new client secret"
+ Copy the newly created client secret to a save place
+ Open your webtrees config.ini.php file and insert the following lines:
```PHP
Github_clientId='xxx'
Github_clientSecret='xxx'
```
+ Insert the configuration details from your Github OAuth2 App into the newly included configuration lines of your config.ini.php file:
    + **Github_clientId**='...' (value shown in Github, like described above)
    + **Github_clientSecret**='...' (value shown in Github, like described above)
+ Press button "Update Application" 

### Google
+ Open the [Google API credential](https://console.cloud.google.com/apis/credentials) page and log into your Google account
+ Select the drop down menu next to the "Google Cloud" icon and select **NEW PROJECT**
+ Enter a project name
+ For organization, choose "No organization"
+ Press the "CREATE" button
+ Enter language/region data (or keep the default) and press the "SAVE" button
+ From the menu on the left side, choose "OAuth consent screen"
+ Select "External" and press "CREATE" button
+ Enter the "App name", "User support email", and "Developer contact information"; do not enter data regarding the app domain
+ Press button "CREATE ÁND CONTINUE"
+ On the pages about scopes and test users, just press "SAVE AND CONTINUE"
+ Finally, press button "BACK TO DASHBOARD"
+ From the menu on the left side, choose "Credentials"
+ Click the "CREATE CREDENTIALS" button (top middle) and select "OAuth client ID"
+ For "Application type", select "Web application"
+ Enter a name
+ At "Authorised redirect URIs", press the "ADD URI" button
+ Enter the following redirect URL and press the "CREATE" button
    + URL: **BASE_URL/index.php?route=/webtrees/OAuth2Client** (BASE_URL from webtrees config.ini.php; base_url='...')
+ From the pop window, copy the **Client ID** and the **Client secret**
+ Copy the client secret to a save place
+ Open your webtrees config.ini.php file and insert the following lines:
```PHP
Google_clientId='xxx'
Google_clientSecret='xxx'
```
+ Insert the configuration details from your Github OAuth2 App into the newly included configuration lines of your config.ini.php file:
    + **Google_clientId**='...' (value shown in Google, like described above)
    + **Google_clientSecret**='...' (value shown in Google, like described above)
+ Press the "OK" button in the Google browser page

### Joomla
+ Download the Joomla extension [joomla-oauth2-server](https://github.com/Jefferson49/joomla-oauth2-server/releases/latest)
+ Install the extension in the Joomla administration backend
+ Open the backend menu: Components / OAuth2 Server / Configure OAuth
+ Click on the button "Add client"
+ Enter a name for "Client Name"
+ Enter the "Authorized Redirect URL" for the webtrees OAuth 2.0 client:
    + URL: **BASE_URL/index.php?route=/webtrees/OAuth2Client** (BASE_URL from webtrees config.ini.php; base_url='...')
+ Check the "Client Name", "Client ID", and "Client Secret" in the list of OAuth clients
+ Open your webtrees config.ini.php file and insert the following lines:
```PHP
Joomla_clientId='xxx'
Joomla_clientSecret='xxx'
Joomla_urlAuthorize='xxx'
Joomla_loginButtonLabel='xxx'
```
+ Insert the configuration details from the Joomla OAuth 2.0 Server into the newly included configuration lines of your config.ini.php file:
    + **Joomla_clientId**='...' (value shown in Joomla, like described above)
    + **Joomla_clientSecret**='...' (value shown in Joomla, like described above)
    + **Joomla_urlAuthorize**='JOOMLA_BASE_URL/index.php' (JOOMLA_BASE_URL from your Joomla installation, e.g. 'https://mysite.net/joomla')
    + **Joomla_loginButtonLabel**='...' (the label, which shall be shown for the login button etc.))

## Webtrees Version
The module was developed and tested with [webtrees 2.1.20](https://webtrees.net/download), but should also run with any other 2.1 version.

## Translation
You can help to translate this module. The translation is based on [gettext](https://en.wikipedia.org/wiki/Gettext) and uses .po files, which can be found in [/resources/lang/](https://github.com/Jefferson49/webtrees-oauth2-client/tree/main/resources/lang). You can use a local editor like [Poedit](https://poedit.net/) or notepad++ to work on translations and provide them in the [Github repository](https://github.com/Jefferson49/webtrees-oauth2-client) of the module. You can do this via a pull request (if you know how to do), or by opening a new issue and attaching a .po file. Updated translations will be included in the next release of this module.

Currently, the following languages are available:
+ English
+ German

## Bugs and Feature Requests
If you experience any bugs or have a feature request for this webtrees custom module, you can [create a new issue](https://github.com/Jefferson49/webtrees-oauth2-client/issues).

## Github Repository
https://github.com/Jefferson49/webtrees-oauth2-client