<?php
/**
 * Class Setup Mask
 * 
 * @package ConLite
 * @subpackage Setup
 * @version $Rev$
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2017, conlite.org
 * 
 * $Id$:
 */
/**
 * @package    ContenidoBackendArea
 * @version    0.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupMask {
    
    /**
     * @var object Template
     */
    protected $_oTpl;
    
    /**
     * @var object Template
     */
    protected $_oStepTemplate;
    
    /**
     * 
     * @var bool
     */
    protected $_bNavigationEnabled = false;
    
    /**
     * 
     * @var string
     */
    protected $_sHeader;
    public mixed $_bBackstep;
    public mixed $_bNextstep;

    /**
     * @param string $sStepTemplate
     * @param int $iStep
     */
    public function __construct(protected $_sStepTemplate, protected $_iStep = false) {
        $this->_oTpl = new Template();
        $this->_oStepTemplate = new Template();
    }

    public function setNavigation($sBackstep, $sNextstep) {
        $this->_bNavigationEnabled = true;
        $this->_bBackstep = $sBackstep;
        $this->_bNextstep = $sNextstep;
    }

    public function setHeader($sHeader) {
        if (array_key_exists("setuptype", $_SESSION)) {
            $sSetupType = $_SESSION["setuptype"];
        } else {
            $sSetupType = "";
        }

        $this->_sHeader = match ($sSetupType) {
            "setup" => "Setup - " . $sHeader,
            "upgrade" => "Upgrade - " . $sHeader,
            "migration" => "Migration - " . $sHeader,
            default => $sHeader,
        };
    }

    public function _createNavigation() {
        $link = new cHTMLLink("#");

        $link->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '" . $this->_bNextstep . "';");
        $link->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");

        $nextSetup = new cHTMLAlphaImage;
        $nextSetup->setSrc("../conlite/images/submit.gif");
        $nextSetup->setMouseOver("../conlite/images/submit_hover.gif");
        $nextSetup->setClass("button");

        $link->setContent($nextSetup);

        if ($this->_bNextstep != "") {
            $this->_oStepTemplate->set("s", "NEXT", $link->render());
        } else {
            $this->_oStepTemplate->set("s", "NEXT", '');
        }

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

    public function render() {
        if ($this->_bNavigationEnabled) {
            $this->_createNavigation();
        }

        if ($this->_iStep !== false) {
            $this->_oTpl->set("s", "STEPS", cGenerateSetupStepsDisplay($this->_iStep));
        } else {
            $this->_oTpl->set("s", "STEPS", "");
        }

        $this->_oTpl->set("s", "HEADER", $this->_sHeader);
        $this->_oTpl->set("s", "TITLE", "ConLite Setup - " . $this->_sHeader);

        $this->_oTpl->set("s", "CONTENT", $this->_oStepTemplate->generate($this->_sStepTemplate, true, false));

        $this->_oTpl->generate("templates/setup.tpl", false, false);
    }
}
?>