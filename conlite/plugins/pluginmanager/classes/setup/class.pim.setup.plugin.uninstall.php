<?php
/**
 * 
 */

class pimSetupPluginUninstall extends pimSetupBase {
    
    const SQL_FILE = "plugin_uninstall.sql";

    public function __construct() {
        parent::__construct();
    }
    
    public function uninstallPlugin($iIdPlugin, $sDeleteSql) {
        // first check if plugin is installed in db
        $oPlugin = new pimPlugin($iIdPlugin);
        if($oPlugin->isLoaded()) { // plugin exists in db
            $this->_iPiId = (int) $iIdPlugin;
            $this->_getRelations();
            if(is_array($this->_aRelations) && count($this->_aRelations) > 0) {
                if($this->_deleteRelationEntries()) {
                    $this->_deleteRelations();
                } else {
                    return FALSE;
                }
            }
            
            if($sDeleteSql == "delete") {
                $this->_getPluginSql();
            }
            
            if($this->doQueries()) {
                return $this->_PimPluginCollection->delete($iIdPlugin);
            }
        }
        return FALSE;
    }
}