<?php
/**
 * 
 * @package CL-Core
 * @subpackage Includes
 * @version $Rev: 330 $
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2015, conlite.org
 * 
 * $Id: include.mod_sync.php 330 2015-06-22 11:59:04Z oldperl $
 */

/* @var $sess Contenido_Session */
/* @var $perm Contenido_Perm */
/* @var $auth Contenido_Challenge_Crypt_Auth */
/* @var $notification Contenido_Notification */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$oPage = new cPage();
$oPage->setHtml5();

if ((int) $client > 0) {
    $aContent = array();
    #######
    # Sync Module
    #######
    $oModuleCollection	= new cApiModuleCollection;
    $oModuleCollection->setWhere("idclient", $client);

    $oModuleCollection->query();
    $iItemCount = $oModuleCollection->count();
    
    // sync all modules if wanted
    if(isset($_GET['syncmod']) && (int) $_GET['syncmod'] == 1) {   
        $iSyncMods = 0;
        $aContent[] = "<h3>".i18n("Syncing Modules!")."</h3>";
        $oTextDiv = new cHTMLDiv();
        /* @var $oModule cApiModule */
        while ($oModule = $oModuleCollection->next()) {
            if(!$oModule->isLoadedFromFile('output') && !$oModule->isLoadedFromFile('input')) {
                $oTextDiv->setContent(i18n("Module ").$oModule->get("name").": ".i18n("nothing to do"));
                $aContent[] = $oTextDiv->render();
                continue;
            }
            if($oModule->isLoadedFromFile('output')) {
                $oModule->set("output", addslashes(stripslashes($oModule->get('output'))));
            }
            if($oModule->isLoadedFromFile('input')) {
                $oModule->set("input", addslashes(stripslashes($oModule->get('input'))));
            }
            $oModule->set("lastmodified", date("Y-m-d H:i:s"));
            $oModule->store();
            $oTextDiv->setContent(i18n("Module ").$oModule->get("name").": ".i18n("synchronized"));
            $aContent[] = $oTextDiv->render();
            unset($oModule);
        }
    }  else {
        $aContent[] = $notification->returnNotification(Contenido_Notification::LEVEL_WARNING, i18n("No modules to sync!"));
    }  
    unset($oModuleCollection);
    $oPage->setContent($aContent);
} else {
    $oPage->setContent($notification->returnNotification(Contenido_Notification::LEVEL_WARNING, i18n("Nothing to do!")));
}
$oPage->render();
?>