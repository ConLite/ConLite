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