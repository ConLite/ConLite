<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Mod Rewrite front_content.php controller. Does some preprocessing jobs, tries
 * to set following variables, depending on mod rewrite configuration and if
 * request part exists:
 * - $client
 * - $changeclient
 * - $lang
 * - $changelang
 * - $idart
 * - $idcat
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend plugins
 * @version    0.1
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since Contenido release 4.8.15
 *
 * {@internal
 *   created  2008-05-xx
 *
 *   $Id: front_content_controller.php 220 2013-02-14 08:40:43Z Mansveld $:
 * }}
 *
 */


defined('CON_FRAMEWORK') or die('Illegal call');


global $client, $changeclient, $cfgClient, $lang, $changelang, $idart, $idcat, $path;

ModRewriteDebugger::add(ModRewrite::getConfig(), 'front_content_controller.php mod rewrite config');


// create an mod rewrite controller instance and execute processing
$oMRController = new ModRewriteController($_SERVER['REQUEST_URI']);
$oMRController->execute();

if ($oMRController->errorOccured()) {

    // an error occured (idcat and or idart couldn't catched by controller)

    $iRedirToErrPage = ModRewrite::getConfig('redirect_invalid_article_to_errorsite', 0);
    // try to redirect to errorpage if desired
    if ($iRedirToErrPage == 1 && (int) $client > 0 && (int) $lang > 0) {
        global $errsite_idcat, $errsite_idart;

        if ($cfgClient['set'] != 'set')    {
            rereadClients();
        }

        // errorpage
        $aParams = array(
            'client' => $client, 'idcat' => $errsite_idcat[$client], 'idart' => $errsite_idart[$client],
            'lang' => $lang, 'error'=> '1'
        );
        $errsite = 'Location: ' . Contenido_Url::getInstance()->buildRedirect($aParams);
        header("HTTP/1.0 404 Not found");
        mr_header($errsite);
        exit();
    }

} else {

    // set some global variables

    if ($oMRController->getClient()) {
        $client = $oMRController->getClient();
    }

    if ($oMRController->getChangeClient()) {
        $changeclient = $oMRController->getChangeClient();
    }

    if ($oMRController->getLang()) {
        $lang = $oMRController->getLang();
    }

    if ($oMRController->getChangeLang()) {
        $changelang = $oMRController->getChangeLang();
    }

    if ($oMRController->getIdArt()) {
        $idart = $oMRController->getIdArt();
    }

    if ($oMRController->getIdCat()) {
        $idcat = $oMRController->getIdCat();
    }

    if ($oMRController->getPath()) {
        $path = $oMRController->getPath();
    }

}

// some debugs
ModRewriteDebugger::add($mr_preprocessedPageError, 'mr $mr_preprocessedPageError', __FILE__);
ModRewriteDebugger::add($idart, 'mr $idart', __FILE__);
ModRewriteDebugger::add($idcat, 'mr $idcat', __FILE__);
ModRewriteDebugger::add($lang, 'mr $lang', __FILE__);
ModRewriteDebugger::add($client, 'mr $client', __FILE__);

