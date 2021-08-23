<?php

/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

if (!(int) $client > 0) {
    #if there is no client selected, display empty page
    $oPage = new cPage();
    $oPage->render();
    return;
}

cInclude("includes", "functions.file.php");

if ($action == "file_delete") {
    if ($perm->have_perm_area_action($area, $action)) {
        $path = $cfgClient[$client]["js"]["path"];
        $sDelFile = filter_input(INPUT_GET, 'delfile', FILTER_SANITIZE_URL);

        if (file_exists($path . $sDelFile)) {
            unlink($path . $sDelFile);
            removeFileInformation($client, $sDelFile, 'js', $db);
        }
    } else {
        $notification->displayNotification("error", i18n("Permission denied"));
    }
}


$oDirList = new cGuiFileList($cfgClient[$client]["js"]["path"], 'js');
$oDirList->scanDir();

$oDirList->renderList();
