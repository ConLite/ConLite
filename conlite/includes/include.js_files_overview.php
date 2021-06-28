<?php

/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


$oDirList = new cGuiFileList($cfgClient[$client]["js"]["path"], 'js');
$oDirList->scanDir();

$oDirList->renderList();