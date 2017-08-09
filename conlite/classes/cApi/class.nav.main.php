<?php

/**
 * 
 * @package Core
 * @subpackage cApiClasses
 * 
 *   $Id$
 */
// security check
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiNavMainCollection extends ItemCollection {

    public function __construct() {
        global $cfg;
        parent::__construct(cRegistry::getConfigValue('tab', 'nav_main'), 'idnavm');
        $this->_setItemClass("cApiNavMain");
    }

    public function create($sName, $sLocation, $iId = NULL) {
        $oItem = $this->createNewItem($iId);
        if ($oItem->isLoaded()) {
            $oItem->set('name', $name);
            $oItem->set('location', $location);
            $oItem->store();
            return $oItem;
        }
    }

}

class cApiNavMain extends Item {

    public function __construct($mId = false) {
        global $cfg;
        parent::__construct(cRegistry::getConfigValue('tab', 'nav_main'), 'idnavm');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}
