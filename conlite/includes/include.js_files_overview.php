<?php

/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

if (!(int) $client > 0) {
  #if there is no client selected, display empty page
  $oPage = new cPage;
  $oPage->render();
  return;
}


$oDirList = new cGuiFileList($cfgClient[$client]["js"]["path"], 'js');
$oDirList->scanDir();

$oDirList->renderList();