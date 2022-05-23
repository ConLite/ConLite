<?php
/**
 * File:
 * class.registry.php
 *
 * Description:
 *  Registry Class
 * 
 * @package Core
 * @subpackage cClasses
 * @version $Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class cRegistry {
    
    /**
     * Function wich returns path after the last possible place changing via
     * configuration file.
     *
     * @return string path
     */
    public static function getBackendPath() {
        $cfg = self::getConfig();
        return $cfg['path']['contenido'];
    }

    /**
     * Function wich returns the backend URL after the last possible place
     * changing via configuration file.
     *
     * @return string URL
     */
    public static function getBackendUrl() {
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
    public static function getFrontendPath() {
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
    public static function getFrontendUrl() {
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
    public static function getClientConfig($iClientId = 0) {
        $aClientConfig = self::_fetchGlobalVariable('cfgClient', array());

        if((int) $iClientId <= 0) {
            return $aClientConfig;
        }

        return (isset($aClientConfig[$iClientId]) ? $aClientConfig[$iClientId] : array());
    }
    
    /**
     * Returns the current client ID stored in the global variable "client".
     *
     * @return int
     */
    public static function getClientId() {
        return self::_fetchGlobalVariable('client', self::_fetchGlobalVariable('load_client', 0));
    }
    
    /**
     * Returns the configuration array stored in the global variable "cfg".
     *
     * @return array
     */
    public static function getConfig() {
        return self::_fetchGlobalVariable('cfg', array());
    }
    
    /**
     * This function returns either a full configuration section or the value
     * for a certain configuration option if a $optionName is given.
     * In this case a $default value can be given which will be returned if this
     * option is not defined.
     *
     * @param string $sectionName
     * @param string $optionName optional
     * @param string $defaultValue optional
     * @return array string
     */
    public static function getConfigValue($sectionName = NULL, $optionName = NULL, $defaultValue = NULL) {
        // get general configuration array
        $cfg = self::getConfig();

        // determine configuration section
        $section = array();
        if (array_key_exists($sectionName, $cfg)) {
            $section = $cfg[$sectionName];
        }
        if (NULL === $optionName) {
            return $section;
        }

        // determine configuration value for certain option name of
        // configuration section
        $value = $defaultValue;
        if (is_array($cfg[$sectionName])) {
            if (array_key_exists($optionName, $section)) {
                $value = $section[$optionName];
            }
        }
        return $value;
    }
    
    public static function getDb() {
        try {
            $oDb = new DB_ConLite();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $oDb;
    }

    /**
     * Fetches the database table name with its prefix.
     *
     * @param string $sIndexName name of the index
     * @return string
     */
    public static function getDbTableName($sIndexName) {
        $aCfgTab = self::getConfigValue("tab");

        if (!is_array($aCfgTab) || !isset($aCfgTab[$sIndexName])) {
            return '';
        }

        return $aCfgTab[$sIndexName];
    }
    
    public static function getArticleId($autoDetect = false) {
        return self::_fetchGlobalVariable('idart', 0);
    }
    
    public static function getArticleLanguageId($autoDetect = false) {
        return self::_fetchGlobalVariable('idartlang', 0);
    }
    
    public static function getCategoryId($autoDetect = false) {
        return self::_fetchGlobalVariable('idcat', 0);
    }
    
    public static function getLanguageId() {
        return self::_fetchGlobalVariable('lang', self::_fetchGlobalVariable('load_lang', 0));
    }
    
    /**
     * Returns path to plugins folder
     * 
     * @return string
     */
    public static function getPluginsPath() {
        return self::getBackendPath().self::getConfigValue('path', 'plugins');
    }

    /**
     * Returns Id for current session
     * 
     * @return string | null
     */
    public static function getSessionId() {
        $sess = self::_fetchGlobalVariable('sess');
        if(is_object($sess)) {
            return $sess->id;  
        }
        return NULL;
    }
    
    /**
     * Checks if backend edit mode active or not
     * 
     * @return bool
     */
    public static function isBackendEditMode() {
        return self::_fetchGlobalVariable('edit', FALSE);
    }

    /**
     * Fetches the global variable requested.
     * If variable is not set, the default value is returned.
     *
     * @param string $variableName name of the global variable
     * @param mixed $defaultValue default value
     * @return mixed
     */
    protected final static function _fetchGlobalVariable($variableName, $defaultValue = NULL) {
        if (!isset($GLOBALS[$variableName])) {
            return $defaultValue;
        }

        return $GLOBALS[$variableName];
    }
    
    protected final static function _fetchItemObject($apiClassName, $objectId) {
        if ((int) $objectId <= 0) {
            throw new cInvalidArgumentException('Object ID must be greater than 0.');
        }

        if (!class_exists($apiClassName)) {
            throw new cInvalidArgumentException('Requested API object was not found: \'' . $apiClassName . '\'');
        }

        return new $apiClassName($objectId);
    }
}
?>