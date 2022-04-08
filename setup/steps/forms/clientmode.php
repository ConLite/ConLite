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
 *   $Id$:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupClientMode extends cSetupMask {

    public function __construct($step, $previous, $next) {
        parent::__construct("templates/setup/forms/clientmode.tpl", $step);
        $this->setHeader(i18n_setup("Example Client"));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("Example Client"));
        $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("If you are new to ConLite, you should create an example client to start working with."));

        cInitializeArrayKey($_SESSION, "clientmode", "");

        $aChoices = array();

        $aChoices["CLIENTEXAMPLES"] = "none"; //i18n_setup("Client with example modules and example content");
        $aChoices["CLIENTMODULES"] = "none"; //i18n_setup("Client with example modules, but without example content");
        $aChoices["CLIENT"] = i18n_setup("Client without examples");
        $aChoices["NOCLIENT"] = i18n_setup("Don't create client");
        
        $sChoiceDefault = "NOCLIENT";

        foreach ($aChoices as $sKey => $sChoice) {
            if ($sChoice == "none") {
                $this->_oStepTemplate->set("s", "CONTROL_" . $sKey, "");
                $this->_oStepTemplate->set("s", "LABEL_" . $sKey, "");
                continue;
            }
            $oRadio = new cHTMLRadiobutton("clientmode", $sKey);
            $oRadio->setLabelText(" ");
            $oRadio->setStyle('width:auto;border:0;');

            if ($_SESSION["clientmode"] == $sKey || ($_SESSION["clientmode"] == "" && $sKey == $sChoiceDefault)) {
                $oRadio->setChecked("checked");
            }

            $oLabel = new cHTMLLabel($sChoice, $oRadio->getId());

            $this->_oStepTemplate->set("s", "CONTROL_" . $sKey, $oRadio->toHtml(false));
            $this->_oStepTemplate->set("s", "LABEL_" . $sKey, $oLabel->render());
        }

        $this->setNavigation($previous, $next);
    }

}

?>