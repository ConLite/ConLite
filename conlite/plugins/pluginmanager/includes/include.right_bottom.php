<?php

/**
 * ConLite PluginManager Backend Overview
 * 
 * based on the excellent work of Frederic Schneider (4fb)
 * adapted and recoded for ConLite by Ortwin Pinke
 *
 * @package    PluginManager
 * @version    $Rev: 41 $
 * @author     Ortwin Pinke <ortwin.pinke@conlite.org>
 * @author     Frederic Schneider
 * @copyright (c) 2008-2015, ConLite.org
 * 
 *   $Id: include.right_bottom.php 41 2018-05-20 21:55:49Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}
/* @var $sess Contenido_Session */
/* @var $perm Contenido_Perm */
/* @var $auth Contenido_Challenge_Crypt_Auth */

$oNoti = new Contenido_Notification();
$aMessages = array();
$oPage = new cPage();
$oPage->sendNoCacheHeaders();
$oPage->setHtml5();
$oPage->addCssFile("plugins/pluginmanager/css/pluginmanager.css");
$oPage->addJsFile("plugins/pluginmanager/scripts/jquery.plainoverlay.js");
$oPage->addJsFile("plugins/pluginmanager/scripts/jquery.plainmodal.js");
$oPage->addJsFile("plugins/pluginmanager/scripts/pluginmanager.js");

// give permission only to sysadmin
/* @var $perm Contenido_Perm */
if (!$perm->isSysadmin()) {
    $oPage->setContent($oNoti->returnNotification(Contenido_Notification::LEVEL_ERROR, i18n("Permission denied!")));
    $oPage->render();
    die();
}

// check disable plugin var
if ($cfg['debug']['disable_plugins'] === true) {
    $oPage->setContent($oNoti->returnNotification(Contenido_Notification::LEVEL_WARNING, i18n('Currently the plugin system is disabled via configuration', "pluginmanager")));
    $oPage->render();
}

$oPimPluginCollection = new pimPluginCollection();
$oView = new pimView();

$sViewAction = isset($_REQUEST['plugin_action']) ? $_REQUEST['plugin_action'] : 'overview';

switch ($sViewAction) {
    case 'uninstall_plugin':
        $iPluginId = (int) $_POST['plugin_id'];
        $oPluginHandler = new pimPluginHandler();
        if($oPluginHandler->loadPluginFromDb($iPluginId)) {
            if($oPluginHandler->uninstallPlugin($_POST['delete_sql'])) {
                $aMessages[] = "info:".i18n("Plugin uninstalled!", "pluginmanager");
            } else {
                $aMessages[] = "error:".i18n("Cannot uninstall Plugin!", "pluginmanager");
            }
        } else {
            $aMessages[] = "error:".i18n("Cannot uninstall Plugin! Plugin not found in Db.", "pluginmanager");
        }
        break;
    case 'upload':
        $aMessages[] = "info:".i18n("Feature not implemented right now!", "pluginmanager");
        /*
        // name of uploaded file
        $sTempFile = Contenido_Security::escapeDB($_FILES['package']['name'], null);

        // path to pluginmanager temp-dir
        $sTempFilePath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'pluginmanager' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        move_uploaded_file($_FILES['package']['tmp_name'], $sTempFilePath . $sTempFile);

        $oExtractor = new Contenido_ArchiveExtractor($sTempFilePath . $sTempFile);

        // xml file validation
        $oSetup->sTempXml = $oExtractor->extractArchiveFileToVariable('plugin.xml');
        $oSetup->checkXml();

        // load plugin.xml to an xml-string
        $oTempXml = simplexml_load_string($oSetup->sTempXml);

        // check min contenido version
        if (!empty($oTempXml->general->min_contenido_version) && version_compare($cfg['version'], $oTempXml->general->min_contenido_version, '<')) {
            throw new Contenido_VersionCompare_Exception(i18n("You have to installed Contenido ") . $oTempXml->general->min_contenido_version . i18n(" or higher to install this plugin!"));
        }

        // build the new plugin dir
        $sTempPluginDir = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $oTempXml->general->plugin_foldername . DIRECTORY_SEPARATOR;

        // add sql inserts
        $oSql = new Contenido_PluginSqlBuilder_Install($cfg, (int) $client, $oTempXml);

        if ($oSetup->bValid === true) {
            $oExtractor->setDestinationPath($sTempPluginDir);
            $oExtractor->extractArchive();
        }

        unlink($sTempPluginDir . 'plugin.xml');
        unlink($sTempFilePath . $sTempFile);
        */
        break;
    default:
        break;
}

// get installed plugins and build view
$aPluginsInstalled = array();
$sPlugins = '';
$oPimPluginCollection->select(NULL, NULL, 'executionorder');
$iNumInstalledPlugins = $oPimPluginCollection->count();
/* @var $oPimPlugin pimPlugin */
while ($oPimPlugin = $oPimPluginCollection->next()) {
    // initalization new class
    $iIdPlugin = $oPimPlugin->get("idplugin");
    $oView2 = new pimView();
    $oPluginHandler = new pimPluginHandler();
    $sPlugins .= $oPluginHandler->getInfoInstalled($iIdPlugin);
    $aPluginsInstalled[] = $oPimPlugin->get("folder");
}

// get plugins extracted but not installed
if (is_dir($cfg['path']['plugins'])) {
    if ($handle = opendir($cfg['path']['plugins'])) {
        $iPiExCount = (int) count($aPluginsInstalled) + 1;
        $pluginsExtracted = '';
        $aNeededTplVar = array(
            "description"=>  i18n("This plugin has no description.", "pluginmanager"),
            "dependencies" => i18n("This plugin has no dependencies.", "pluginmanager"),
            "license" => i18n("License not set.", "pluginmanager")
        );
        
        $aDefaultLang = array(
                    'lang_foldername' => i18n('Foldername', 'pluginmanager'),
                    'lang_author' => i18n("Author", "pluginmanager"),
                    'lang_contact' => i18n("Contact", "pluginmanager"),
                    'lang_license' => i18n("License", "pluginmanager"),
                    'lang_dependencies' => i18n("Dependencies", "pluginmanager"),
                    'lang_writeable' => i18n("Writable", "pluginmanager")            
        );
        
        while ($pluginFoldername = readdir($handle)) {
            $pluginPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $pluginFoldername;
            $bPiPathWritable = (is_writable($pluginPath))?TRUE:FALSE;
            $sPiCfg = $pluginPath . '/cl_plugin.xml';

            if (is_dir($pluginPath) && file_exists($sPiCfg) && !in_array($pluginFoldername, $aPluginsInstalled)) {               
                
                // get infos from plugin.xml
                $oPluginHandler = new pimPluginHandler();
                if(FALSE === $oPluginHandler->loadXmlFile($sPiCfg)) {
                    $aMessages[] = "error:".sprintf(i18n('Invalid Xml document for %s. Please contact the plugin author.', 'pluginmanager'),$sPiCfg);
                    continue;
                }
                //echo "<pre>";
                //print_r($oPluginHandler->getCfgXmlObject());
                $aNeededTplVar['writeable'] = ($bPiPathWritable)?i18n("Everything looks fine.", "pluginmanager"):'<span style="color:red;">'
                    .i18n("Pluginfolder not writable!", "pluginmanager").'</span>';
                $aInfoGeneral = array_merge($aNeededTplVar, $oPluginHandler->getPiGeneralArray());
                //echo "<pre>";
                //print_r($aInfoGeneral);
                
                // initalization new template class
                $oView2 = new pimView();
                $oView2->setVariable($iPiExCount, "PLUGIN_NUMBER");
                $oView2->setMultiVariables($aInfoGeneral);
                $aLang = array(
                    'FOLDERNAME' => $pluginFoldername,
                    'INSTALL_LINK' => $sess->url('main.php?area='.$area.'&frame=4&pim_view=install-extracted&pluginFoldername=' . $pluginFoldername)
                );
                $oView2->setMultiVariables(array_merge($aLang, $aDefaultLang));                
                
                $oView2->setTemplate('pi_manager_extracted_plugins.html');
                $pluginsExtracted .= $oView2->getRendered(1);
                $iPiExCount++;
            }
        }
        closedir($handle);
    }
}

// if pluginsExtracted var is empty
if (empty($pluginsExtracted)) {
    $pluginsExtracted = i18n('No entries', 'pim');
}


// added language vars
$oView->setVariable(i18n("Add new plugin", 'pluginmanager'), 'LANG_ADD');
$oView->setVariable(i18n("Please choose a plugin package", 'pluginmanager'), 'LANG_ADD_CHOOSE');
$oView->setVariable(i18n("Upload plugin package", 'pluginmanager'), 'LANG_ADD_UPLOAD');
$oView->setVariable(i18n("Remove marked Plugins", 'pluginmanager'), 'LANG_DELETE');
$oView->setVariable(i18n("Installed Plugins", 'pluginmanager'), 'LANG_INSTALLED');
$oView->setVariable(i18n("Update marked plugins", 'pluginmanager'), 'LANG_UPDATE');
$oView->setVariable(i18n('Plugins to install', 'pluginmanager'), 'LANG_EXTRACTED');
$oView->setVariable(i18n('Drag & Drop plugin to list of installed plugins to install it.', 'pluginmanager'), 'LANG_HINT_EXTRACTED');

// added installed plugins to pi_manager_overview
$oView->setVariable($iNumInstalledPlugins, 'INSTALLED_PLUGINS');
$oView->setVariable($sPlugins, 'PLUGINS');
$oView->setVariable($pluginsExtracted, 'PLUGINS_EXTRACTED');

//print_r($aMessages);
// show overview page
$oView->setTemplate('pi_manager_overview.html');
$sMessages = "";
if(count($aMessages) > 0 ) {
    $sMessages .= '<ul id="pim_messages" style="display:none">';
    foreach($aMessages as $sMessage) {
        $sMessages .= "<li>".$sMessage."</li>";
    }
    $sMessages .= "</ul>";
}
//$oView->getRendered();
$oPage->setContent(array($oView->getRendered(1), $sMessages));
$oPage->render();
?>