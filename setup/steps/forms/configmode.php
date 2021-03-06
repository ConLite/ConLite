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
 *   $Id$:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupConfigMode extends cSetupMask {

    public function __construct($step, $previous, $next) {
        if ($_SESSION["setuptype"] == "setup") {
            parent::__construct("templates/setup/forms/configmode.tpl", $step);
        } else {
            parent::__construct("templates/setup/forms/configmodewopass.tpl", $step);
        }
        $this->setHeader(i18n_setup("config.php mode"));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("config.php mode"));
        $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please choose 'save' or 'download'"));
        $this->_oStepTemplate->set("s", "LABEL_DESCRIPTION", i18n_setup("ConLite requires a configuration file called 'config.php'. This file will be generated by the setup automatically if the filesystem permissions are correct. If 'save' is activated by default, setup can save the file config.php. If not, 'download' is activated by default, and you have to place the file in the directory &quot;contenido/includes/&quot; manually when a download prompt appears. The download prompt appears at the end of the setup process."));

        $oConfigSave = new cHTMLRadiobutton("configmode", "save");
        $oConfigSave->setStyle('width:auto;border:0;');

        $oConfigDownload = new cHTMLRadiobutton("configmode", "download");
        $oConfigDownload->setStyle('width:auto;border:0;');

        if (canWriteFile("../conlite/includes/config.php")) {
            $oConfigSave->setChecked(true);
        } else {
            $oConfigDownload->setChecked(true);
        }


        $oConfigSaveLabel = new cHTMLLabel(i18n_setup("Save"), $oConfigSave->getId());
        $this->_oStepTemplate->set("s", "CONTROL_SAVE", $oConfigSave->toHtml(false));
        $this->_oStepTemplate->set("s", "CONTROL_SAVELABEL", $oConfigSaveLabel->render());

        $oConfigDownloadLabel = new cHTMLLabel(i18n_setup("Download"), $oConfigDownload->getId());
        $this->_oStepTemplate->set("s", "CONTROL_DOWNLOAD", $oConfigDownload->toHtml(false));
        $this->_oStepTemplate->set("s", "CONTROL_DOWNLOADLABEL", $oConfigDownloadLabel->render());

        $this->setNavigation($previous, $next);
    }

    public function _createNavigation() {
        $link = new cHTMLLink("#");

        if ($this->_bNextstep == "doinstall") {
            /* Install launcher */
        }

        $link->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '" . $this->_bNextstep . "'; document.setupform.submit();");

        $nextSetup = new cHTMLAlphaImage;
        $nextSetup->setSrc("../conlite/images/submit.gif");
        $nextSetup->setMouseOver("../conlite/images/submit_hover.gif");
        $nextSetup->setClass("button");

        $link->setContent($nextSetup);

        $this->_oStepTemplate->set("s", "NEXT", $link->render());

        $backlink = new cHTMLLink("#");
        $backlink->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '" . $this->_bBackstep . "';");
        $backlink->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");

        $backSetup = new cHTMLAlphaImage;
        $backSetup->setSrc("images/controls/back.gif");
        $backSetup->setMouseOver("images/controls/back.gif");
        $backSetup->setClass("button");
        $backSetup->setStyle("margin-right: 10px");
        $backlink->setContent($backSetup);
        $this->_oStepTemplate->set("s", "BACK", $backlink->render());
    }

}

?>