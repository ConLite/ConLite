<?php

class cModuleInputHelper {

    static private $_aOptions = NULL;

    /**
     *
     * @var cHTMLSelectElement 
     */
    static private $_oSelectElement;

    static public function getCategorySelect($sName, $iWidth = "", $sID = "", $bDisabled = false, $iTabIndex = null, $sAccessKey = "") {
        self::$_oSelectElement = new cHTMLSelectElement($sName, $iWidth, $sID, $bDisabled, $iTabIndex, $sAccessKey);
        self::_addCategories();
        if (isset(self::$_aOptions['default'])) {
            self::$_oSelectElement->setDefault(self::$_aOptions['default']);
        }

        $sSelectScript = '<script type="text/javascript">'
                . '$(function() {'
                . '$("#' . self::$_oSelectElement->getID() . '").selectmenu().selectmenu("widget").addClass("overflow");'
                . '});'
                . '</script>';

        // reset static options array
        self::_setDefaultOptions();
        return self::$_oSelectElement->render() . $sSelectScript;
    }
    
    static public function getFileSelect($sName) {
        global $cCurrentModule;
        if(is_null(self::$_aOptions)) {
            self::_setDefaultOptions();
        }
        self::$_oSelectElement = new cHTMLSelectElement($sName);
        if(empty(self::$_aOptions['file_select_dir'])) {
            $sDir = cRegistry::getClientConfig(cRegistry::getClientId())['path']['frontend'].'templates';
        } else {
            $sDir = self::$_aOptions['file_select_dir'];
        }
        
        $aFiles = scanDirectory($sDir);
        $aModFiles = array();
        
        // add template files in mod dir if present
        if(null !== cRegistry::getConfigValue('dceModEdit','use') && cRegistry::getConfigValue('dceModEdit','use') == TRUE) {
            //echo "modul: ".$cCurrentModule;
            $oModule = new cApiModule($cCurrentModule);
            if($oModule->isLoaded() && $oModule->isLoadedFromFile('input')) {
                $sModTplPath = $oModule->getModulePath()."template";
                if(is_dir($sModTplPath)) {
                    $aModFiles = scanDirectory($sModTplPath);
                    //print_r($aModFiles);
                    $aFiles = array_merge($aFiles, $aModFiles);
                }
            }
        }
        //echo '<pre>';
        //print_r($aFiles);
        
        $oOption = new cHTMLOptionElement("--Please Choose--", 0);
        self::$_oSelectElement->addOptionElement(0, $oOption);
        
        foreach($aFiles as $iKey=>$sFilePath) {
            $aParts = pathinfo($sFilePath);
            if(!in_array($aParts['extension'], self::$_aOptions['file_extension'])) {
                continue;
            }
            if($aParts['dirname'] == $sDir) {
                $oOption = new cHTMLOptionElement($aParts['basename'], $aParts['basename']);
            } else {
                $oOption = new cHTMLOptionElement("Mod: ".$aParts['basename'], $aParts['basename']);
            }
            self::$_oSelectElement->addOptionElement($iKey+1, $oOption);
            //print_r($aParts);
        }
        if (isset(self::$_aOptions['default'])) {
            self::$_oSelectElement->setDefault(self::$_aOptions['default']);
        }
        
        $sSelectScript = '<script type="text/javascript">'
                . '$(function() {'
                . '$("#' . self::$_oSelectElement->getID() . '").selectmenu().selectmenu("widget").addClass("overflow");'
                . '});'
                . '</script>';

        // reset static options array
        self::_setDefaultOptions();
        return self::$_oSelectElement->render().$sSelectScript;
    }

    static public function getOnOffButton($sName) {
        global $cnumber;
        $oDiv = new cHTMLDiv();
        $oDiv->setID($oDiv->getID()."C".$cnumber);
        $sInput = "";
        $aValues = array(
            'false' => mi18n("OFF"),
            'true' => mi18n("ON")
        );
        $oInput = new cHTMLRadiobutton($sName, "not set");

        foreach ($aValues as $sValue => $sLabel) {
            $oInput->advanceID();
            $oInput->setLabelText($sLabel);
            $oInput->setAttribute("value", $sValue);
            if (self::$_aOptions['default'] == $sValue) {
                $oInput->setChecked(TRUE);
            } else {
                $oInput->setChecked(FALSE);
            }

            $sInput .= $oInput->render();
        }
        $sInput .= '<script type="text/javascript">'
                . '$(function() {'
                . '$("#' . $oDiv->getID() . '").buttonset();'
                . '});'
                . '</script>';


        $oDiv->setContent($sInput);
        return $oDiv->render();
    }

    static public function setOptions(array $aOptions) {
        self::_setDefaultOptions();
        self::$_aOptions = array_merge(self::$_aOptions, $aOptions);
    }

    static protected function _addCategories() {

        $oDB = new DB_ConLite();

        $sSQL = "SELECT tblCat.idcat AS idcat, tblCatLang.name AS name, "
                . "tblCatLang.visible AS visible, tblCatLang.public AS public, tblCatTree.level AS level "
                . "FROM " . cRegistry::getConfigValue('tab', 'cat')
                . " AS tblCat, " . cRegistry::getConfigValue('tab', 'cat_lang') . " AS tblCatLang, "
                . cRegistry::getConfigValue('tab', 'cat_tree') . " AS tblCatTree"
                . " WHERE tblCat.idclient = '" . Contenido_Security::escapeDB(cRegistry::getClientId(), $oDB) . "'"
                . " AND tblCatLang.idlang = '" . Contenido_Security::escapeDB(cRegistry::getLanguageId(), $oDB) . "'"
                . " AND tblCatLang.idcat = tblCat.idcat AND tblCatTree.idcat = tblCat.idcat ";

        if (self::$_aOptions['max_level'] > 0) {
            $sSQL .= "AND tblCatTree.level < '" . Contenido_Security::escapeDB(self::$_aOptions['max_level'], $oDB) . "' ";
        }
        $sSQL .= "ORDER BY tblCatTree.idtree";

        $oDB->query($sSQL);

        $iCount = (int) $oDB->num_rows();
        if ($iCount > 0) {
            $iCountOptions = count(self::$_oSelectElement->_options);

            while ($oDB->next_record()) {
                $sSpaces = "";
                $sStyle = "";
                $iID = $oDB->f("idcat");

                for ($i = 0; $i < $oDB->f("level"); $i++) {
                    $sSpaces .= "&nbsp;&nbsp;&nbsp;";
                }

                // Generate new option element
                if ((self::$_aOptions['cat_visible'] && $oDB->f("visible") == 0) ||
                        (self::$_aOptions['cat_public'] && $oDB->f("public") == 0)) {
                    // If category has to be visible or public and it isn't, don't add value
                    $sValue = "";
                } else if (self::$_aOptions['with_articles']) {
                    // If article will be added, set negative idcat as value
                    $sValue = "-" . $iID;
                } else {
                    // Show only categories - and everything is fine...
                    $sValue = $iID;
                }
                $oOption = new cHTMLOptionElement($sSpaces . ">&nbsp;" . utf8_encode($oDB->f("name")), $sValue);
                if ((self::$_aOptions['cat_visible'] && $oDB->f("visible") == 0) ||
                        (self::$_aOptions['cat_public'] && $oDB->f("public") == 0)) {
                    $oOption->setDisabled(true);
                }
                // Coloring option element, restricted shows grey color
                $oOption->setStyle("background-color: #EFEFEF");
                if (self::$_aOptions['colored'] && ($oDB->f("visible") == 0 || $oDB->f("public") == 0)) {
                    $oOption->setStyle("color: #666666;");
                }

                // Add option element to the list
                self::$_oSelectElement->addOptionElement($iCountOptions, $oOption);
                /*
                  if ($bWithArt) {
                  $iArticles = $this->addArticles($iID, $bColored, self::$_aOptions['art_online'], $sSpaces);
                  $iCount += $iArticles;
                  } */
                $iCountOptions++;
            }
        }
        return $iCount;
    }

    static private function _setDefaultOptions() {
        self::$_aOptions = array(
            'max_level' => 0,
            'default' => NULL,
            'colored' => FALSE,
            'cat_visible' => TRUE,
            'cat_public' => TRUE,
            'with_articles' => FALSE,
            'art_online' => TRUE,
            'file_select_dir' => NULL,
            'file_extension' => array('html')
        );
    }

}
