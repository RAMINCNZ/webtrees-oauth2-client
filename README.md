[![Latest Release](https://img.shields.io/github/v/release/Jefferson49/OAuth2ClienForWebtrees?display_name=tag)](https://github.com/Jefferson49/ExtendedImportExport/releases/latest)
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.1.x-green)](https://webtrees.net/download)

# OAuth2 Client for webtrees
A [webtrees](https://webtrees.net) 2.1 custom module to provide [OAuth2](https://en.wikipedia.org/wiki/OAuth) single sign on ([SSO](https://en.wikipedia.org/wiki/Single_sign-on)).

##  Table of contents
This README file contains the following main sections:
+   [What are the benefits of this module?](#what-are-the-benefits-of-this-module)
+   [Installation](#installation)
+   [Webtrees Version](#webtrees-version)
+   [Supported Authorization Providers](#supported-authorization-providers)
+   [Configuration of Authorization Providers](#configuration-of-authorization-providers)
    + [Github](#github)
    + [Google](#google)
    + [Joomla](#joomla)
+   [Translation](#translation)
+   [Bugs and Feature Requests](#bugs-and-feature-requests)
+   [Github Repository](#github-repository)

## What are the benefits of this module?

+ The module provides single sign on ([SSO](https://en.wikipedia.org/wiki/Single_sign-on)) into webtrees based on the [OAuth2](https://en.wikipedia.org/wiki/OAuth) standard.
+ A pre-configured set of certain authorization can be selected during webtrees login.
+ If using sign on with an authorization provider, the user account data (i.e. name, user name, email) from the authorization provider is used in webtrees.

## Supported Authorization Providers

Currently, the following authorization providers are supported:
+ Github
+ Google
+ Joomla (with a specific authorization provider installed in Joomla)

## Configuration of Authorization Providers

### Github

### Google

### Joomla

## Installation
+ Download the [latest release](https://github.com/Jefferson49/OAuth2ClienForWebtrees/releases/latest) of the module
+ Copy the folder "oauth2_client" into the "module_v4" folder of your webtrees installation
+ Check if the module is activated in the control panel:
  + Login to webtrees as an administrator
	+ Go to "Control Panel/All Modules", and find the module called "ExtendedImportExport"
	+ Check if it has a tick for "Enabled"

## Webtrees Version
The module was developed and tested with [webtrees 2.1.20](https://webtrees.net/download), but should also run with any other 2.1 version.

## Translation
You can help to translate this module. The translation is based on [gettext](https://en.wikipedia.org/wiki/Gettext) and uses .po files, which can be found in [/resources/lang/](https://github.com/Jefferson49/OAuth2ClienForWebtrees/tree/main/resources/lang). You can use a local editor like [Poedit](https://poedit.net/) or notepad++ to work on translations and provide them in the [Github repository](https://github.com/Jefferson49/OAuth2ClienForWebtrees) of the module. You can do this via a pull request (if you know how to do), or by opening a new issue and attaching a .po file. Updated translations will be included in the next release of this module.

Currently, the following languages are already available:
+ English
+ German

## Bugs and Feature Requests
If you experience any bugs or have a feature request for this webtrees custom module, you can [create a new issue](https://github.com/Jefferson49/OAuth2ClienForWebtrees/issues).

## Github Repository
https://github.com/Jefferson49/OAuth2ClienForWebtrees