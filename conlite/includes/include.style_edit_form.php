<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Edit file
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.2
 * @author     Olaf Niemann, Willi Mann
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created 2003-04-20
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *   modified 2008-08-14, Timo Trautmann, Bilal Arslan - Functions for versionning and storing file meta data added
 *
 *   $Id$:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


cInclude("external", "edit_area/class.edit_area.php");

$sFileType = "css";

$sActionCreate = 'style_create';
$sActionEdit = 'style_edit';

$page = new cPage;
$page->setEncoding("utf-8");

$tpl->reset();

if (!$perm->have_perm_area_action($area, $action)) {
    $notification->displayNotification("error", i18n("Permission denied"));
} else if (!(int) $client > 0) {
    #if there is no client selected, display empty page
    $page->render();
} else {
    $path = $cfgClient[$client]["css"]["path"];
    if (stripslashes($_REQUEST['file'])) {
        $sReloadScript = "<script type=\"text/javascript\">
                             var left_bottom = parent.parent.frames['left'].frames['left_bottom'];
                             if (left_bottom) {
                                 var href = left_bottom.location.href;
                                 href = href.replace(/&file.*/, '');
                                 left_bottom.location.href = href+'&file='+'" . $_REQUEST['file'] . "';

                             }
                         </script>";
    } else {
        $sReloadScript = "";
    }

    $sTempFilename = stripslashes($_REQUEST['tmp_file']);
    $sOrigFileName = $sTempFilename;

    if (getFileType($_REQUEST['file']) != $sFileType AND strlen(stripslashes(trim($_REQUEST['file']))) > 0) {
        $sFilename .= stripslashes($_REQUEST['file']) . ".$sFileType";
    } else {
        $sFilename .= stripslashes($_REQUEST['file']);
    }

    if (stripslashes($_REQUEST['file'])) {
        $sReloadScript = "<script type=\"text/javascript\">
                             var left_bottom = parent.parent.frames['left'].frames['left_bottom'];
                             if (left_bottom) {
                                 var href = left_bottom.location.href;
                                 href = href.replace(/&file[^&]*/, '');
                                 left_bottom.location.href = href+'&file='+'" . $sFilename . "';

                             }
                         </script>";
    } else {
        $sReloadScript = "";
    }

    // Content Type is css
    $sTypeContent = "css";
    $aFileInfo = getFileInformation($client, $sTempFilename, $sTypeContent, $db);

    # create new file
    if ($_REQUEST['action'] == $sActionCreate AND $_REQUEST['status'] == 'send') {
        $sTempFilename = $sFilename;
        createFile($sFilename, $path);
        $bEdit = fileEdit($sFilename, $_REQUEST['code'], $path);
        updateFileInformation($client, $sFilename, 'css', $auth->auth['uid'], $_REQUEST['description'], $db);
        $sReloadScript .= "<script type=\"text/javascript\">
                 var right_top = top.content.right.right_top;
                 if (right_top) {
                     var href = '" . $sess->url("main.php?area=$area&frame=3&file=$sTempFilename") . "';
                     right_top.location.href = href;
                 }
                 </script>";
    }

    # edit selected file
    if ($_REQUEST['action'] == $sActionEdit AND $_REQUEST['status'] == 'send') {
        if ($sFilename != $sTempFilename) {
            $sTempFilename = renameFile($sTempFilename, $sFilename, $path);
            $sReloadScript .= "<script type=\"text/javascript\">
                 var right_top = top.content.right.right_top;
                 if (right_top) {
                     var href = '" . $sess->url("main.php?area=$area&frame=3&file=$sTempFilename") . "';
                     right_top.location.href = href;
                 }
                 </script>";
        } else {
            $sTempFilename = $sFilename;
        }


        updateFileInformation($client, $sOrigFileName, 'css', $auth->auth['uid'], $_REQUEST['description'], $db, $sFilename);

        /**
         * START TRACK VERSION
         * */
        // For read Fileinformation an get the id of current File
        cInclude("includes", "functions.file.php");

        if ((count($aFileInfo) == 0) || ((int) $aFileInfo["idsfi"] == 0)) {
            $aFileInfo = getFileInformation($client, $sTempFilename, $sTypeContent, $db);
            $aFileInfo['description'] = '';
        }

        if ((count($aFileInfo) == 0) || ($aFileInfo["idsfi"] != "")) {
            $oVersion = new VersionFile($aFileInfo["idsfi"], $aFileInfo, $sFilename, $sTypeContent, $cfg, $cfgClient, $db, $client, $area, $frame, $sOrigFileName);
            // Create new version
            $oVersion->createNewVersion();
        }

        /**
         * END TRACK VERSION
         * */
        $bEdit = fileEdit($sFilename, $_REQUEST['code'], $path);
    }

    # generate edit form 
    if (isset($_REQUEST['action'])) {

        $sAction = ($bEdit) ? $sActionEdit : $_REQUEST['action'];

        if ($_REQUEST['action'] == $sActionEdit) {
            $sCode = getFileContent($sFilename, $path);
        } else {
            $sCode = empty($_REQUEST['code'])?'':$_REQUEST['code'];
        }

        $form = new UI_Table_Form("file_editor");
        $form->addHeader(i18n("Edit file"));
        $form->setWidth("100%");
        $form->setVar("area", $area);
        $form->setVar("action", $sAction);
        $form->setVar("frame", $frame);
        $form->setVar("status", 'send');
        $form->setVar("tmp_file", $sTempFilename);

        $tb_name = new cHTMLTextbox("file", $sFilename, 60);
        $form->add(i18n("Name"), $tb_name);
        
        $ta_code = new cHTMLTextarea("code", clHtmlSpecialChars($sCode), 100, 35, "code");
        $ta_code->updateAttributes(array("wrap" => getEffectiveSetting('style_editor', 'wrap', 'off')));
        $ta_code->setStyle("font-family: monospace;width: 100%;");
        $form->add(i18n("Code"), $ta_code);
        
        $aFileInfo = getFileInformation($client, $sTempFilename, "css", $db);
        if(!empty($aFileInfo["description"])) {
            $sDescription = clHtmlSpecialChars($aFileInfo["description"]);
        } else {
            $sDescription = '';
        }
        
        $descr = new cHTMLTextarea("description", $sDescription, 100, 5);

        $descr->setStyle("font-family: monospace;width: 100%;");
        $form->add(i18n("Description"), $descr->render());

        $page->setContent($form->render());

        $oEditArea = new EditArea('code', 'css', substr(strtolower($belang), 0, 2), true, $cfg);
        $page->addScript('editarea', $oEditArea->renderScript());

        $page->addScript('reload', $sReloadScript);
        $page->render();
    }
}