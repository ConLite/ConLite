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
class pimPluginRelationCollection extends ItemCollection {
    
    const REL_AREA = 'area';
    const REL_ACTION = 'action';
    const REL_NAVS = 'navs';
    const REL_CTYPE = 'ctype';
   
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['plugins_rel'], 'idpluginrelation');
        $this->_setItemClass("pimPluginRelation");
    }
    
    public function create($idItem, $idPlugin, $type) {
        // create a new entry
        $item = parent::create();
        $item->set('iditem', $idItem);
        $item->set('idplugin', $idPlugin);
        $item->set('type', $type);

        $item->store();
        return $item;
    }
    
    public function getRelations($idPlugin, $type=NULL) {        
        return;
    }
    
    public function deleteRelations($idPlugin, $type=NULL) {
        return;
    }
}

class pimPluginRelation extends Item {    

    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['plugins_rel'], 'idpluginrelation');
        
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>