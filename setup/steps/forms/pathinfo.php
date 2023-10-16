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

class cSetupPath extends cSetupMask {

    public function __construct($step, $previous, $next) {
        parent::__construct("templates/setup/forms/pathinfo.tpl", $step);
        $this->setHeader(i18n_setup("System Directories"));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("System Directories"));
        $this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please check the directories identified by the system. If you need to change a path, click on the name and enter the new path in the available input box."));

        [$root_path, $root_http_path] = getSystemDirectories(true);

        $cHTMLErrorMessageList = new cHTMLErrorMessageList;
        $cHTMLErrorMessageList->setStyle("width: 580px; height: 200px; overflow: auto; border: 1px solid black;");
        $cHTMLFoldableErrorMessages = [];

        [$a_root_path, $a_root_http_path] = getSystemDirectories();
        $oRootPath = new cHTMLTextbox("override_root_path", $a_root_path);
        $oRootPath->setWidth(100);
        $oRootPath->setClass("small");
        $oWebPath = new cHTMLTextbox("override_root_http_path", $a_root_http_path);
        $oWebPath->setWidth(100);
        $oWebPath->setClass("small");

        $cHTMLFoldableErrorMessages[0] = new cHTMLFoldableErrorMessage(i18n_setup("ConLite Root Path") . ":<br>" . $root_path, $oRootPath);
        $cHTMLFoldableErrorMessages[0]->_oContent->setStyle("padding-bottom: 8px;");
        $cHTMLFoldableErrorMessages[1] = new cHTMLFoldableErrorMessage(i18n_setup("ConLite Web Path") . ":<br>" . $root_http_path, $oWebPath);
        $cHTMLFoldableErrorMessages[1]->_oContent->setStyle("padding-bottom: 8px;");

        $cHTMLErrorMessageList->setContent($cHTMLFoldableErrorMessages);


        $this->_oStepTemplate->set("s", "CONTROL_PATHINFO", $cHTMLErrorMessageList->render());

        $this->setNavigation($previous, $next);
    }

}

?>