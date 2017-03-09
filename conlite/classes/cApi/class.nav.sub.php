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

class cApiNavSubCollection extends ItemCollection {
    public function __construct() {
        global $cfg;
        parent::__construct(cRegistry::getConfigValue('tab', 'nav_sub'), 'idnavs');
        $this->_setItemClass("cApiNavSub");
    }
}

class cApiNavSub extends Item {
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct(cRegistry::getConfigValue('tab', 'nav_sub'), 'idnavs');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}