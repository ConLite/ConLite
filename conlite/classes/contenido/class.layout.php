<?php
/**
 * File:
 * class.layout.php
 *
 * Description:
 *  cApi class
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev: 352 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.layout.php 352 2015-09-24 12:12:51Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

cInclude('includes', 'functions.upl.php');

class cApiLayoutCollection extends ItemCollection {

    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["lay"], "idlay");
        $this->_setItemClass("cApiLayout");
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiLayoutCollection() {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct();
    }

    public function create($title) {
        global $client;
        $item = parent::createNewItem();
        $item->set("name", $title);
        $item->set("idclient", $client);
        $item->store();
        return ($item);
    }

}

class cApiLayout extends Item {
    
    protected $_sLayPath;
    
    protected $_bFromFile = FALSE;


    /**
     * Configuration Array of LayFileEdit
     * array is set with entries in local config file
     * 
     * @var array 
     */
    private $_aLayFileEditConf = array(        
        'use'=>false,
        'layFolderName'=>'data/layouts'
    );
    
    private $_sLayoutAlias;

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["lay"], "idlay");
        $this->setFilters(array(), array());
        
        if(isset($cfg['dceLayEdit']) && is_array($cfg['dceLayEdit'])) {
            $this->_aLayFileEditConf = array_merge($this->_aLayFileEditConf, $cfg['dceLayEdit']);
            $this->_setLayPath();
        }
        
        $oClient = new cApiClient($client);
        $aClientProp = $oClient->getPropertiesByType('layfileedit');
        if(count($aClientProp) > 0) {
            $this->_aLayFileEditConf = array_merge($this->_aLayFileEditConf, $aClientProp);
        }
        //print_r($this->_aLayFileEditConf);
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
    
    public function getLayout() {
        if($this->_aLayFileEditConf['use'] === TRUE) {
            $sLayFile = $this->_aLayFileEditConf['layPath']
                    .$this->_sLayoutAlias."/".$this->_sLayoutAlias."html";
            $this->_setCodeFromFile($sLayFile);
        }
        return $this->get("code");
    }


    protected function _getLayPath() {
        return $this->_aLayFileEditConf['layPath'];
    }
    
    protected function _setLayPath() {
        global $cfgClient, $client;
        if(!isset($this->_aLayFileEditConf['layPath']) 
                || empty($this->_aLayFileEditConf['layPath'])) {
            $this->_aLayFileEditConf['layPath'] = $cfgClient[$client]["path"]["frontend"]
                        .$this->_aLayFileEditConf['layFolderName']."/";
        }        
    }
    
    protected function _getLayoutAlias() {
        if(empty($this->_sLayoutAlias)) {
            if($this->virgin) {
                return false;
            } else {
                $this->_setLayoutAlias();
            }
        }
        return $this->_sLayoutAlias;
    }
    
    protected function _setLayoutAlias() {
        $this->_sLayoutAlias = strtolower(uplCreateFriendlyName($this->get('name')));
    }
    
    protected function _onLoad() {        
            if($this->virgin !== TRUE) {
                $this->_setLayoutAlias();
                $this->_setLayPath();
            }
    }
    
    /**
     * read file and set an object field
     * 
     * @param string $sFile 
     * @param string $sField
     * @return boolean
     */
    private function _setCodeFromFile($sFile) {
        if(FALSE === strstr($sFile, $this->_aLayFileEditConf['layPath'])) {
            $sFile = $this->_aLayFileEditConf['layPath'].$sFile;
        }
        $sLayDirPath = $this->_aLayFileEditConf['layPath'].$this->_sLayoutAlias."/";
        
        if(is_dir($sLayDirPath) && is_writable($sLayDirPath)
                && file_exists($sLayDirPath.$this->_sLayoutAlias.".html")) {
            $sFile = $sLayDirPath.$this->_sLayoutAlias.".html";
        }

        if(is_file($sFile) && is_readable($sFile)) {
            $iFileSize = (int) filesize($sFile);
            if($iFileSize > 0 && $fh = fopen($sFile, 'r')) {
                $this->set("code", fread($fh, $iFileSize), false);
                fclose($fh);
                $this->_bFromFile = TRUE;
                $this->_displayNoteFromFile();
                return true;
            }
        }        
        return false;
    }
    
    private function _displayNoteFromFile($bIsOldPath = FALSE) {
        global $frame, $area;
        if($frame == 4 && $area == 'lay_edit') {
            $sAddMess = '';
            $oNote = new Contenido_Notification();
            $oNote->displayNotification('warning', i18n("Layout uses LayFromFile. Editing and Saving may not be possible in backend."));
        }
    }
}
?>