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

class cSetupInstaller extends cSetupMask {

    public function __construct($step) {
        parent::__construct("templates/setup/forms/installer.tpl", $step);
        $this->setHeader(i18n_setup("System Installation"));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("System Installation"));
        $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("ConLite will be installed, please wait ..."));

        $this->_oStepTemplate->set("s", "DBUPDATESCRIPT", "dbupdate.php");

        switch ($_SESSION["setuptype"]) {
            case "setup":
                $this->_oStepTemplate->set("s", "DONEINSTALLATION", i18n_setup("Setup completed installing. Click on next to continue."));
                $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Setup is installing, please wait..."));
                $_SESSION["upgrade_nextstep"] = "setup8";
                $this->setNavigation("", "setup8");
                break;
            case "upgrade":
                $this->_oStepTemplate->set("s", "DONEINSTALLATION", i18n_setup("Setup completed upgrading. Click on next to continue."));
                $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Setup is upgrading, please wait..."));
                $_SESSION["upgrade_nextstep"] = "ugprade7";
                $this->setNavigation("", "upgrade7");
                break;
            case "migration":
                $this->_oStepTemplate->set("s", "DONEINSTALLATION", i18n_setup("Setup completed migration. Click on next to continue."));
                $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Setup is migrating, please wait..."));
                $_SESSION["upgrade_nextstep"] = "migration8";
                $this->setNavigation("", "migration8");
                break;
        }
    }

}

?>