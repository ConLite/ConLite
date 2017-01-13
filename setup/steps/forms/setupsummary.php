<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * 
 * Requirements: 
 * @con_php_req 5
 *
 * @package    ContenidoBackendArea
 * @version    0.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id: setupsummary.php 137 2012-10-02 12:00:00Z oldperl $:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupSetupSummary extends cSetupMask {

    public function __construct($step, $previous, $next) {
        parent::__construct("templates/setup/forms/setupsummary.tpl", $step);
        $this->setHeader(i18n_setup("Summary"));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("Summary"));
        $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please check your settings and click on the next button to start the installation"));

        $cHTMLErrorMessageList = new cHTMLErrorMessageList;

        switch ($_SESSION["setuptype"]) {
            case "setup":
                $sType = i18n_setup("Setup");
                break;
            case "upgrade":
                $sType = i18n_setup("Upgrade");
                break;
            case "migration":
                $sType = i18n_setup("Migration");
                break;
        }

        switch ($_SESSION["configmode"]) {
            case "save":
                $sConfigMode = i18n_setup("Save");
                break;
            case "download":
                $sConfigMode = i18n_setup("Download");
                break;
        }

        $messages = array(
            i18n_setup("Installation type") . ":" => $sType,
            i18n_setup("Database parameters") . ":" => i18n_setup("Database host") . ": " . $_SESSION["dbhost"] . "<br>" . i18n_setup("Database name") . ": " . $_SESSION["dbname"] . "<br>" . i18n_setup("Database username") . ": " . $_SESSION["dbuser"] . "<br>" . i18n_setup("Database prefix") . ": " . $_SESSION["dbprefix"],
        );

        if ($_SESSION["setuptype"] == "setup") {
            $aChoices = array("CLIENTEXAMPLES" => i18n_setup("Client with example modules and example content"),
                "CLIENTMODULES" => i18n_setup("Client with example modules but without example content"),
                "CLIENT" => i18n_setup("Client without examples"),
                "NOCLIENT" => i18n_setup("Don't create a client"));
            $messages[i18n_setup("Client installation") . ":"] = $aChoices[$_SESSION["clientmode"]];
        }

        // additional plugins
        $aPlugins = $this->_getSelectedAdditionalPlugins();
        if (count($aPlugins) > 0) {
            $messages[i18n_setup("Additional Plugins") . ":"] = implode('<br>', $aPlugins);
            ;
        }

        $cHTMLFoldableErrorMessages = array();

        foreach ($messages as $key => $message) {
            $cHTMLFoldableErrorMessages[] = new cHTMLInfoMessage($key, $message);
        }

        $cHTMLErrorMessageList->setContent($cHTMLFoldableErrorMessages);

        $this->_oStepTemplate->set("s", "CONTROL_SETUPSUMMARY", $cHTMLErrorMessageList->render());

        $this->setNavigation($previous, $next);
    }

    public function _getSelectedAdditionalPlugins() {
        $aPlugins = array();
        if ($_SESSION['plugin_newsletter'] == 'true') {
            $aPlugins[] = i18n_setup('Newsletter');
        }
        if ($_SESSION['plugin_content_allocation'] == 'true') {
            $aPlugins[] = i18n_setup('Content Allocation');
        }
        if ($_SESSION['plugin_mod_rewrite'] == 'true') {
            $aPlugins[] = i18n_setup('Mod Rewrite');
        }
        return $aPlugins;
    }

}

?>