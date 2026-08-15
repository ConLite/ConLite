<?php
/**
 * ConLite Registry Class
 *
 * @package ConLite\System
 * @since V3.0.0
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2026, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 */

namespace ConLite\System;

use ConLite\Database\DbConLite;
use ConLite\Exceptions\Exception;
use ConLite\Exceptions\InvalidArgumentException;
use Contenido_Challenge_Crypt_Auth;
use Contenido_Perm;

defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * ConLite Registry
 *
 * @since V3.0.0
 */
class Registry
{
    /**
     * Returns the configuration array stored in the global variable "cfg".
     *
     * @return array
     */
    public static function getConfig(): array
    {
        return self::_fetchGlobalVariable('cfg', []);
    }

    /**
     * Function wich returns path after the last possible place changing via
     * configuration file.
     *
     * @return string path
     */
    public static function getBackendPath(): string
    {
        $cfg = self::getConfig();
        return $cfg['path']['conlite'];
    }

    /**
     * @return Contenido_Challenge_Crypt_Auth|null
     */
    public static function getAuth(): ?Contenido_Challenge_Crypt_Auth
    {
        $value = self::_fetchGlobalVariable('auth');

        return $value instanceof Contenido_Challenge_Crypt_Auth ? $value : null;
    }

    /**
     * @return string
     */
    public static function getArea(): string
    {
        return (string) self::_fetchGlobalVariable('area');
    }

    /**
     * @return string
     */
    public static function getAction(): string
    {
        return (string) self::_fetchGlobalVariable('action');
    }



    /**
     * Function wich returns the backend URL after the last possible place
     * changing via configuration file.
     *
     * @return string URL
     */
    public static function getBackendUrl(): string
    {
        $cfg = self::getConfig();
        return $cfg['path']['contenido_fullhtml'];
    }

    /**
     * Function which returns path after the last possible place changing via
     * configuration file.
     * The path point to the current client
     *
     * @author konstantinos.katikakis
     * @return string
     *         path
     */
    public static function getFrontendPath(): string
    {
        $cfgClient = self::getClientConfig();
        $client = self::getClientId();
        return (empty($cfgClient))?'':$cfgClient[$client]['path']['frontend'];
    }

    /**
     * Function which returns URL after the last possible place changing via
     * configuration file.
     * The path point to the current client
     *
     * @author konstantinos.katikakis
     * @return string
     *         URL
     */
    public static function getFrontendUrl(): string
    {
        $cfgClient = self::getClientConfig();
        $client = self::getClientId();
        return $cfgClient[$client]['path']['htmlpath'];
    }

    /**
     * Returns the client configuration array stored in the global variable
     * "cfgClient".
     * If no client ID is specified or is 0 the complete array is returned.
     *
     * @param int $iClientId Client ID (optional)
     * @return array Array with Client Configurations
     */
    public static function getClientConfig(int $iClientId = 0): array
    {
        $aClientConfig = self::_fetchGlobalVariable('cfgClient', []);

        if($iClientId <= 0) {
            return $aClientConfig;
        }

        return ($aClientConfig[$iClientId] ?? []);
    }

    /**
     * Returns the current client ID stored in the global variable "client".
     *
     * @return int
     */
    public static function getClientId(): int
    {
        return self::_fetchGlobalVariable('client', self::_fetchGlobalVariable('load_client', 0));
    }


    /**
     * This function returns either a full configuration section or the value
     * for a certain configuration option if a $optionName is given.
     * In this case a $default value can be given which will be returned if this
     * option is not defined.
     */
    public static function getConfigValue(string $sectionName, ?string $optionName = NULL, mixed $defaultValue = NULL): mixed
    {
        // get general configuration array
        $cfg = self::getConfig();

        // determine configuration section
        $section = [];
        if (array_key_exists($sectionName, $cfg)) {
            $section = $cfg[$sectionName];
        }
        if (NULL === $optionName) {
            return $section;
        }

        $value = $defaultValue;
        if (is_array($cfg[$sectionName])) {
            if (array_key_exists($optionName, $section)) {
                $value = $section[$optionName];
            }
        }
        return $value;
    }

    /**
     * returns db instance
     * @param array $cfgDb
     * @return DbConLite|void
     */
    public static function getDb(array $cfgDb = [])
    {
        try {
            $db = new DbConLite($cfgDb);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $db;
    }

    /**
     * Fetches the database table name with its prefix.
     *
     * @param string $indexName name of the index
     * @return string
     */
    public static function getDbTableName(string $indexName): string
    {
        $aCfgTab = self::getConfigValue("tab");

        return $aCfgTab[$indexName] ?? '';
    }

    /**
     * Fetch current article id
     *
     * @return int
     */
    public static function getArticleId(): int
    {
        return (int) self::_fetchGlobalVariable('idart', 0);
    }

    /**
     * fetch current article language id
     *
     * @return int
     */
    public static function getArticleLanguageId(): int
    {
        return (int) self::_fetchGlobalVariable('idartlang', 0);
    }

    /**
     * fetch current category id
     *
     * @return int
     */
    public static function getCategoryId(): int
    {
        return (int) self::_fetchGlobalVariable('idcat', 0);
    }

    /**
     * fetch language id
     *
     * @return int
     */
    public static function getLanguageId(): int
    {
        return (int) self::_fetchGlobalVariable('lang', self::_fetchGlobalVariable('load_lang', 0));
    }

    /**
     * Returns path to plugins folder
     *
     * @return string
     */
    public static function getPluginsPath(): string
    {
        return self::getBackendPath() . self::getConfigValue('path', 'plugins');
    }

    /**
     * Returns Id for current session
     *
     * @return string | null
     */
    public static function getSessionId(): ?string
    {
        $sess = self::_fetchGlobalVariable('sess');

        if(is_object($sess)) {
            return $sess->id;
        }
        return null;
    }

    /**
     * Checks if backend edit mode active or not
     *
     * @return bool
     */
    public static function isBackendEditMode(): bool
    {
        return self::_fetchGlobalVariable('edit', false);
    }

    /**
     * fetch id of current module
     *
     * @return int
     */
    public static function getCurrentModule(): int
    {
        return (int) self::_fetchGlobalVariable('cCurrentModule', 0);
    }


    /**
     * @return Contenido_Perm|null
     */
    public static function getPerm(): ?Contenido_Perm
    {
        $value = self::_fetchGlobalVariable('perm');

        return $value instanceof Contenido_Perm ? $value : null;
    }


    /**
     * Fetches the global variable requested.
     * If variable is not set, the default value is returned.
     *
     * @param string $variableName name of the global variable
     * @param mixed $defaultValue default value
     * @return mixed
     */
    protected final static function _fetchGlobalVariable(string $variableName, mixed $defaultValue = NULL): mixed
    {
        if (!isset($GLOBALS[$variableName])) {
            return $defaultValue;
        }

        return $GLOBALS[$variableName];
    }

    /**
     * @throws InvalidArgumentException
     */
    protected final static function _fetchItemObject($apiClassName, $objectId) {
        if ((int) $objectId <= 0) {
            throw new InvalidArgumentException('Object ID must be greater than 0.');
        }

        if (!class_exists($apiClassName)) {
            throw new InvalidArgumentException('Requested API object was not found: \'' . $apiClassName . '\'');
        }

        return new $apiClassName($objectId);
    }
}