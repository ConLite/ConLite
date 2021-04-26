<?php
/**
 * 
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of class.pim.plugin
 *
 * @author Ortwin Pinke <o.pinke@php-backoffice.de>
 */
class pimPluginCollection extends ItemCollection {
   
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['plugins'], 'idplugin');
        $this->_setItemClass("pimPlugin");
    }
}

class pimPlugin extends Item {
    
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['plugins'], 'idplugin');
        
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>