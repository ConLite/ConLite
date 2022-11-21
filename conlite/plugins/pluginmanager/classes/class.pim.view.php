<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Backend View of Contenido PluginManager
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend plugins
 * @version    1.0.0
 * @author     Rudi Bieller
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release 4.8.7
 * 
 * {@internal 
 *   created 2008-03-17
 *   modified 2008-07-04, Frederic Schneider, add security fix and tpl settings
 *
 *   $Id: class.pim.view.php 11 2015-07-14 12:34:24Z oldperl $:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

class pimView extends Template{
    
	protected $sPathToTpl;
	protected $bIsGenerated;
        protected $_sTplPath;
        
        public function __construct($tags = false, $parser = false) {
            $this->reset();
            $this->set('s', 'SESSID', cRegistry::getSessionId());
            $this->bIsGenerated = false;
            $this->_sTplPath  = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR;
            parent::__construct($tags, $parser);
        }
  
	public function setTemplate($sTplName) {
		$this->sPathToTpl = $this->_sTplPath.$sTplName;
	}
        
        public function setMultiVariables($aVariables) {
            if(is_array($aVariables)) {
                foreach($aVariables as $sName=>$sContent) {
                    if(is_numeric($sName)) {
                        continue;
                    }
                    $this->setVariable($sContent, strtoupper($sName));
                }
            }
        }

	public function setVariable($sVariable, $sName = '') {    
		if(empty($sName)) {
			$sName = strtoupper($$sVariable);
		}
                if(is_array($sVariable)) {
                    $sVariable = json_encode($sVariable);
                }
		$this->set('s', $sName, $sVariable);
	}

	public function getRendered($mode = '') {
		$this->bIsGenerated = true;
		return $this->generate($this->sPathToTpl, $mode);
	}
    
	public function __destruct() {
		if ($this->bIsGenerated === false) {
			$this->generate($this->sPathToTpl, true, false);
		}
	}

}
?>