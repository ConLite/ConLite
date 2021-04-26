<?php
/**
 * 
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');


class pimPluginDummy {
    
    public function __construct() {
        ;
    }
    
    public function get($what) {
        $return = FALSE;
        switch($what) {
            case 'folder':
                $return = 'pluginmanager';
                break;
            
            case 'active':
                $return = TRUE;
                break;
        }
        return $return;
    }
    
    public function isLoaded() {
        return TRUE;
    }
}
?>