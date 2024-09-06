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

use Fisharebest\Webtrees\Webtrees;
use Jefferson49\Webtrees\Module\OAuth2Client\Provider\AuthorizationProviderInterface;

use ReflectionMethod;

use function file_exists;
use function parse_ini_file;


/**
 * Factory for an OAuth2 authorization provider with a defined interface for webtrees integration
 */
class AuthorizationProviderFactory
{
    /**
     * Create an OAuth2 authorization provider
     * 
     * @param string $name       name of the authorization provider
     * @param string $base_url   webtrees base URL
     * 
     * @return AuthorizationProviderInterface   A configured authorization provider. Null, if error 
     */
    public function make(string $name, string $base_url) : ?AuthorizationProviderInterface
    {
        $name_space = str_replace('\\\\', '\\',__NAMESPACE__ ) .'\\Provider\\';
        $options = self::readProviderOptionsFromConfigFile($name);

        //If no options found
        if (sizeof($options) === 0) {
            return null;
        }

        $provider_names = self::getAuthorizatonProviderNames();

        foreach($provider_names as $class_name => $provider_name) {
            if ($provider_name === $name) {
                $class_name = $name_space . $class_name;
                return new $class_name($base_url, $options);
            }
        }

        //If no provider found
        return null;
    }

	/**
     * Return the names of all available authorization providers
     *
     * @return array array<class_name => provider_name>
     */ 

    public static function getAuthorizatonProviderNames(): array {

        $provider_names = [];
        $name_space = str_replace('\\\\', '\\',__NAMESPACE__ ) .'\\Provider\\';

        foreach (get_declared_classes() as $class_name) { 
            if (strpos($class_name, $name_space) !==  false) {
                if (in_array($name_space . 'AuthorizationProviderInterface', class_implements($class_name))) {
                    $reflectionMethod = new ReflectionMethod($class_name, 'getName');
                    $class_name = str_replace($name_space, '', $class_name, );
                    $provider_names[$class_name] = $reflectionMethod->invoke(null);
                }
            }
        }

        return $provider_names;
    }

	/**
     * Reads the options of the provider from the webtrees config.ini.php file
     * 
     * @param string $name  Authorization provider name
     * @return array        An array with the options. Empty if options could not be read completely.
     */ 

    public static function readProviderOptionsFromConfigFile(string $name): array {

        $options = [];
        $name_space = str_replace('\\\\', '\\',__NAMESPACE__ ) .'\\Provider\\';
        $provider_names = self::getAuthorizatonProviderNames();

        foreach ($provider_names as $class_name => $provider_name) {
            if ($provider_name === $name) {
                $reflectionMethod = new ReflectionMethod($name_space . $class_name, 'getOptionNames');
                $option_names = $reflectionMethod->invoke(null);
                break;
            }
        }

        // Read the configuration settings
        if (file_exists(Webtrees::CONFIG_FILE)) {
            $config = parse_ini_file(Webtrees::CONFIG_FILE);

            foreach ($config as $key => $value) {

                $key = str_replace($name . '_', '', $key);

                if (in_array($key, $option_names)) {
                    $options[$key] = $value;
                }
            }
        }
        else {
            return [];
        }

        //Check, if complete configuration was found
        foreach ($option_names as $option_name) {
            if (!key_exists($option_name, $options)) {
                return [];
            }
        }

        return $options;
    }
}
