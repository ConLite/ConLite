<?php
/**
 *   $Id:$
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class PluginmanagerAjax extends pimAjax {
    
    /**
     * 
     * @param string $Request
     * @return string
     */
    public function handle($Request) {
        $sString = '';
        switch ($Request) {
            
            // toggle active/inactive of plugins
            case 'toggle_active':
                if(!isset($_POST['plugin_id']) || empty($_POST['plugin_id'])) {
                    $sString = "Error:missing plugin id!   (Err20)";
                    break;
                }
                $iPluginId = (int) $_POST['plugin_id'];
                $oPlugin = new pimPlugin($iPluginId);
                if($oPlugin->isLoaded()) {
                    $iCurrentStat = (int) $oPlugin->get('active');
                    $iNewStat = ($iCurrentStat == 1)?0:1;
                    $oPlugin->set('active', $iNewStat);
                    if($oPlugin->store()) {
                        $sString = "Ok:".$iNewStat;
                        if($iNewStat) {
                            $sString .= ":".i18n("Plugin is active", "pluginmanager");
                        } else {
                            $sString .= ":".i18n("Plugin not active", "pluginmanager");
                        }
                        break;
                    }                    
                }
                $sString = "Error:no changes!  (Err21)";
                break;                
            
            // save sortorder of plugins
            case 'pim_save_sort':
                parse_str($_REQUEST['plugins'], $aPlugins);
                //print_r($aPlugins['plugin']);
                if(is_array($aPlugins['plugin']) && count($aPlugins['plugin']) > 0) {
                    foreach($aPlugins['plugin'] as $sortorder=>$pluginid) {
                        $oPlugin = new pimPlugin($pluginid);
                        $oPlugin->set('executionorder', $sortorder);
                        $oPlugin->store();
                    }
                }
                $sString = "Ok:executionorder saved";
                break;
                
            // install plugin with existing source in plugin dir    
            case 'pim_install': 
                //sleep(3);
                $iNewPluginId = 0;
                $sPluginPath = cRegistry::getBackendPath()
                    .cRegistry::getConfigValue('path', 'plugins')
                    .Contenido_Security::escapeDB($_POST['plugin_folder']).DIRECTORY_SEPARATOR;
                
                if(is_dir($sPluginPath) && is_readable($sPluginPath."cl_plugin.xml")) {
                    $oPluginHandler = new pimPluginHandler();
                    if($oPluginHandler->loadXmlFile($sPluginPath."cl_plugin.xml")) {
                        if($oPluginHandler->installPlugin(Contenido_Security::escapeDB($_POST['plugin_folder']))) {
                            $iNewPluginId = $oPluginHandler->getPluginId();
                            if($iNewPluginId > 0) {
                                $sString = "Ok:".$iNewPluginId.":Plugin successfully installed!";
                            } else {
                                $sString = "Error:".$iNewPluginId.":Plugin not installed! (Err10)";
                            }
                        } else {
                            $sString = "Error:0:Plugin not installed! (Err12)";
                        }
                    } else {
                        $sString = "Error:0:Plugin xml-file missing or not correct! (Err13)";
                    }
                    break;
                }
                $sString = "Error:0:Plugin folder missing or no readable xml-file found! (Err14)";
                break;
                
            // return info about installed plugin    
            case 'pim_get_info_installed':
                $oPluginHandler = new pimPluginHandler();
                $sString = $oPluginHandler->getInfoInstalled((int) $_POST['plugin_id']);
                break;
            
            //if action is unknown generate error message
            default:
                $sString = "Unknown Ajax Action! (Err01)";
                break;
        }
        return $sString;
    }

}
