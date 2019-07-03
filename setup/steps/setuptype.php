<?php

/**
 * Project: 
 * Contenido Content Management System
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


unset($_SESSION["setuptype"]);

class cSetupTypeChooser extends cSetupMask {

    public function __construct() {
        parent::__construct("templates/setuptype.tpl");
        $this->setHeader(i18n_setup("Please choose your setup type"));
        $this->_oStepTemplate->set("s", "TITLE_SETUP", i18n_setup("Install new version"));
        $this->_oStepTemplate->set("s", "VERSION_SETUP", sprintf(i18n_setup("Version %s"), C_SETUP_VERSION));
        $this->_oStepTemplate->set("s", "DESCRIPTION_SETUP", sprintf(i18n_setup("This setup type will install %s."), C_SETUP_VERSION) . "<br><br>" . i18n_setup("Please choose this type if you want to start with an empty or an example installation.") . "<br><br>" . i18n_setup("Recommended for new projects."));

        $this->_oStepTemplate->set("s", "TITLE_UPGRADE", i18n_setup("Upgrade existing installation"));
        $this->_oStepTemplate->set("s", "VERSION_UPGRADE", sprintf(i18n_setup("Upgrade to %s"), C_SETUP_VERSION));
        $this->_oStepTemplate->set("s", "DESCRIPTION_UPGRADE", i18n_setup("This setup type will upgrade your existing installation (ConLite 1.0.x/Contenido 4.6.x or later required).") . "<br><br>" . i18n_setup("Recommended for existing projects."));

        $this->_oStepTemplate->set("s", "TITLE_MIGRATION", i18n_setup("Migrate existing installation"));
        $this->_oStepTemplate->set("s", "VERSION_MIGRATION", sprintf(i18n_setup("Migrate (Version %s)"), C_SETUP_VERSION));
        $this->_oStepTemplate->set("s", "DESCRIPTION_MIGRATION", i18n_setup("This setup type will help you migrating an existing installation to another server.") . "<br><br>" . i18n_setup("Recommended for moving projects across servers."));

        $link = new cHTMLLink("#");


        $nextSetup = new cHTMLAlphaImage;
        $nextSetup->setSrc("../conlite/images/submit.gif");
        $nextSetup->setMouseOver("../conlite/images/submit_hover.gif");
        $nextSetup->setClass("button");

        $link->setContent($nextSetup);

        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value = 'setup1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value = 'setup';");
        $link->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");


        $this->_oStepTemplate->set("s", "NEXT_SETUP", $link->render());

        $link = new cHTMLLink("#");


        $nextSetup = new cHTMLAlphaImage;
        $nextSetup->setSrc("../conlite/images/submit.gif");
        $nextSetup->setMouseOver("../conlite/images/submit_hover.gif");
        $nextSetup->setClass("button");

        $link->setContent($nextSetup);

        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value = 'upgrade1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value = 'upgrade';");
        $link->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");
        $this->_oStepTemplate->set("s", "NEXT_UPGRADE", $link->render());

        $link = new cHTMLLink("#");


        $nextSetup = new cHTMLAlphaImage;
        $nextSetup->setSrc("../conlite/images/submit.gif");
        $nextSetup->setMouseOver("../conlite/images/submit_hover.gif");
        $nextSetup->setClass("button");

        $link->setContent($nextSetup);

        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value = 'migration1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value = 'migration';");
        $link->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");
        $this->_oStepTemplate->set("s", "NEXT_MIGRATION", $link->render());
    }

}

$cSetupStep1 = new cSetupTypeChooser;
$cSetupStep1->render();
?>