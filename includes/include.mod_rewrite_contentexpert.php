<?php
/**
 * Plugin cl-mod-rewrite backend include file to administer expert (in content frame)
 *
 * @package     plugin
 * @subpackage  Mod Rewrite
 * @version     SVN Revision $Rev: 128 $
 * @id          $Id: include.mod_rewrite_contentexpert.php 128 2019-07-03 11:58:28Z oldperl $:
 * @author      Murat Purc <murat@purc.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

global $client, $cfg;

################################################################################
##### Initialization

if ((int) $client <= 0) {
    // if there is no client selected, display empty page
    $oPage = new cPage;
    $oPage->render();
    return;
}

$action = (isset($_REQUEST['mr_action'])) ? $_REQUEST['mr_action'] : 'index';
$debug = false;


################################################################################
##### Some variables


$oMrController = new ModRewrite_ContentExpertController();

$aMrCfg = ModRewrite::getConfig();

$aHtaccessInfo = ModRewrite::getHtaccessInfo();

// define basic data contents (used for template)
$oView = $oMrController->getView();

// view variables
$oView->copy_htaccess_css = 'display:table-row;';
$oView->copy_htaccess_error = '';
$oView->copy_htaccess_contenido_chk = ' checked="checked"';
$oView->copy_htaccess_cms_chk = '';
$oView->contenido_full_path = $aHtaccessInfo['contenido_full_path'];
$oView->client_full_path = $aHtaccessInfo['client_full_path'];
$oView->content_after = '';

$oMrController->setProperty('htaccessInfo', $aHtaccessInfo);

// view language variables
$oView->lng_plugin_functions = i18n("Plugin functions", "cl-mod-rewrite");

$oView->lng_copy_htaccess_type = i18n("Copy/Download .htaccess template", "cl-mod-rewrite");
$oView->lng_copy_htaccess_type_lbl = i18n("Select .htaccess template", "cl-mod-rewrite");
$oView->lng_copy_htaccess_type1 = i18n("Restrictive .htaccess", "cl-mod-rewrite");
$oView->lng_copy_htaccess_type2 = i18n("Simple .htaccess", "cl-mod-rewrite");
$oView->lng_copy_htaccess_type_info1 = i18n("Contains rules with restrictive settings.<br>All requests pointing to extension avi, css, doc, flv, gif, gzip, ico, jpeg, jpg, js, mov, <br>mp3, pdf, png, ppt, rar, txt, wav, wmv, xml, zip, will be excluded vom rewriting.<br>Remaining requests will be rewritten to front_content.php,<br>except requests to 'contenido/', 'setup/', 'cms/upload', 'cms/front_content.php', etc.<br>Each resource, which has to be excluded from rewriting must be specified explicitly.", "cl-mod-rewrite");

$oView->lng_copy_htaccess_type_info2 = i18n("Contains a simple collection of rules. Each requests pointing to valid symlinks, folders or<br>files, will be excluded from rewriting. Remaining requests will be rewritten to front_content.php", "cl-mod-rewrite");

$oView->lng_copy_htaccess_to = i18n("and copy to", "cl-mod-rewrite");
$oView->lng_copy_htaccess_to_contenido = i18n("CONTENIDO installation directory", "cl-mod-rewrite");
$oView->lng_copy_htaccess_to_contenido_info = i18n("Copy the selected .htaccess template into CONTENIDO installation directory<br><br>&nbsp;&nbsp;&nbsp;&nbsp;{CONTENIDO_FULL_PATH}.<br><br>This is the recommended option for a CONTENIDO installation with one or more clients<br>who are running on the same domain.", "cl-mod-rewrite");
$oView->lng_copy_htaccess_to_contenido_info = str_replace('{CONTENIDO_FULL_PATH}', $oView->contenido_full_path, $oView->lng_copy_htaccess_to_contenido_info);
$oView->lng_copy_htaccess_to_client = i18n("client directory", "cl-mod-rewrite");
$oView->lng_copy_htaccess_to_client_info = i18n("Copy the selected .htaccess template into client's directory<br><br>&nbsp;&nbsp;&nbsp;&nbsp;{CLIENT_FULL_PATH}.<br><br>This is the recommended option for a multiple client system<br>where each client has it's own domain/subdomain", "cl-mod-rewrite");
$oView->lng_copy_htaccess_to_client_info = str_replace('{CLIENT_FULL_PATH}', $oView->client_full_path, $oView->lng_copy_htaccess_to_client_info);
$oView->lng_or = i18n("or", "cl-mod-rewrite");
$oView->lng_download = i18n("Download", "cl-mod-rewrite");
$oView->lng_download_info = i18n("Download selected .htaccess template to copy it to the destination folder<br>or to take over the settings manually.", "cl-mod-rewrite");

$oView->lng_resetaliases = i18n("Reset category-/ and article aliases", "cl-mod-rewrite");
$oView->lng_resetempty_link = i18n("Reset only empty aliases", "cl-mod-rewrite");
$oView->lng_resetempty_info = i18n("Only empty aliases will be reset, existing aliases, e. g. manually set aliases, will not be changed.", "cl-mod-rewrite");
$oView->lng_resetall_link = i18n("Reset all aliases", "cl-mod-rewrite");
$oView->lng_resetall_info = i18n("Reset all category-/article aliases. Existing aliases will be overwritten.", "cl-mod-rewrite");
$oView->lng_note = i18n("Note", "cl-mod-rewrite");
$oView->lng_resetaliases_note = i18n("This process could require some time depending on amount of categories/articles.<br>The aliases will not contain the configured plugin separators, but the CONTENIDO default separators '/' und '-', e. g. '/category-word/article-word'.<br>Execution of this function ma be helpful to prepare all or empty aliases for the usage by the plugin.", "cl-mod-rewrite");

$oView->lng_discard_changes = i18n("Discard changes", "cl-mod-rewrite");
$oView->lng_save_changes = i18n("Save changes", "cl-mod-rewrite");


################################################################################
##### Action processing

if ($action == 'index') {
    $oMrController->indexAction();
} elseif ($action == 'copyhtaccess') {
    $oMrController->copyHtaccessAction();
} elseif ($action == 'downloadhtaccess') {
    $oMrController->downloadHtaccessAction();
    exit();
} elseif ($action == 'reset') {
    $oMrController->resetAction();
} elseif ($action == 'resetempty') {
    $oMrController->resetEmptyAction();
} else {
    $oMrController->indexAction();
}


################################################################################
##### Output

$oMrController->render(
    $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'cl-mod-rewrite/templates/contentexpert.html'
);

