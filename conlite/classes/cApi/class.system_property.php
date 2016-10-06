<?php

/**
 * File:
 * class.system_property.php
 *
 * Description:
 *  
 * 
 * @package Core
 * @subpackage cApiClasses
 * @version $Rev: 302M $
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.system_property.php 302M 2016-07-21 16:39:58Z (local) $
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of class.system_properties
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cApiSystemPropertyCollection extends ItemCollection {

    public static $_aCachedProperties;
    public static $_Counter;

    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['system_prop'], 'idsystemprop');
        $this->_setItemClass("cApiSystemProperty");
        if ($this->_bDebug) {
            echo "<pre>";
            print_r(cApiSystemPropertyCollection::$_aCachedProperties);
            echo "</pre>";
        }
    }

    public function getSystemProperty($sType, $sName) {
        // sanitize param
        $sType = trim($sType);
        $sName = trim($sName);
        // use cache if exists
        if (is_array(self::$_aCachedProperties) && key_exists($sType, self::$_aCachedProperties)) {
            if (is_array(self::$_aCachedProperties[$sType]) && key_exists($sName, self::$_aCachedProperties[$sType])) {
                return self::$_aCachedProperties[$sType][$sName]['value'];
            }
        }
        $this->_loadPropertiesByType($sType);

        if (isset(self::$_aCachedProperties[$sType]) && is_array(self::$_aCachedProperties[$sType]) && key_exists($sName, self::$_aCachedProperties[$sType])) {
            return self::$_aCachedProperties[$sType][$sName]['value'];
        }
        return false;
    }

    private function _loadPropertiesByType($sType) {
        $this->resetQuery();
        $this->setWhere('type', $sType);
        $this->query();
        if ($this->count() > 0) {
            self::$_aCachedProperties[$sType] = array();
            /* $oProperty cApiSystemProperty */
            while ($oProperty = $this->next()) {
                self::$_aCachedProperties[$sType][$oProperty->get('name')]['value'] = $oProperty->get('value');
                self::$_aCachedProperties[$sType][$oProperty->get('name')]['idsystemprop'] = $oProperty->get('idsystemprop');
            }
        }
    }

}

class cApiSystemProperty extends Item {

    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['system_prop'], 'idsystemprop');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>