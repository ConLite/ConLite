<?php

/**
 * 
 * @version $Rev: 39 $
 * 
 * $Id: class.pim.plugin.handler.php 39 2018-01-10 07:49:57Z oldperl $
 */

use ConLite\Migrate\cSecurity;

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class pimPluginHandler {

    protected int $_iPluginId = 0;
    protected ?pimPlugin $_oPlugin = NULL;
    protected $_bIsLoaded = FALSE;
    protected string $_sPluginPath;

    /**
     * holds the xml of plugin.xml
     */
    protected ?SimpleXMLElement $_oPiXml = NULL;

    /**
     *
     * @var DomDocument 
     */
    protected DOMDocument $_oDomDocument;
    protected string $_xsd = 'plugins/pluginmanager/xml/plugin_info.xsd';
    protected bool $_bValid = false;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_oDomDocument = new DOMDocument();
        $this->_oDomDocument->preserveWhiteSpace = false;
    }

    /**
     * 
     * @param int $iPluginId
     * @return boolean
     */
    public function loadPluginFromDb($iPluginId): bool
    {
        $this->_oPlugin = new pimPlugin($iPluginId);
        if ($this->_oPlugin->isLoaded()) {
            $this->_iPluginId = $this->_oPlugin->get('idplugin');
            $this->_sPluginPath = cRegistry::getBackendPath()
                    . cRegistry::getConfigValue('path', 'plugins')
                    . $this->_oPlugin->get('folder')
                    . "/";
            return TRUE;
        }
        $this->_oPlugin = NULL;
        return FALSE;
    }

    /**
     * 
     * @param string $sPluginFolderName
     * @return boolean
     */
    public function installPlugin(string $sPluginFolderName): bool
    {
        if (empty($sPluginFolderName)) {
            return FALSE;
        }
        $pluginPath = cRegistry::getBackendPath()
                . cRegistry::getConfigValue('path', 'plugins')
                . $sPluginFolderName
                . "/";

        if (is_null($this->getCfgXmlObject())) {
            $sPiCfg = $pluginPath . 'cl_plugin.xml';
            if (is_dir($pluginPath) && file_exists($sPiCfg)) {
                $this->loadXmlFile($sPiCfg);
            } else {
                return FALSE;
            }
        }

        $oPluginInstaller = new pimSetupPluginInstall();
        $oPluginInstaller->setXsdFile($this->_xsd);
        $oPluginInstaller->setXmlObject($this->getCfgXmlObject());
        $oPluginInstaller->setPluginPath($pluginPath);
        $this->_iPluginId = $oPluginInstaller->installPlugin();
        if ($this->_iPluginId > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 
     * @param string $sHandleSql
     * @return boolean
     */
    public function uninstallPlugin($sHandleSql): bool
    {
        $oPluginUninstall = new pimSetupPluginUninstall();
        $oPluginUninstall->setPluginPath($this->_sPluginPath);
        return $oPluginUninstall->uninstallPlugin($this->_iPluginId, $sHandleSql);
    }

    /**
     * 
     * @return int
     */
    public function getPluginId(): int
    {
        return $this->_iPluginId;
    }

    /**
     *
     * @param string $sFile
     * @return boolean
     * @throws pimXmlStructureException
     */
    public function loadXmlFile(string $sFile): bool
    {
        $this->_oDomDocument->load($sFile);
        if ($this->_validateXml()) {
            $this->_oPiXml = simplexml_load_string($this->_oDomDocument->C14N());
        }
        return is_a($this->_oPiXml, "SimpleXMLElement");
    }


    public function getCfgXmlObject(): ?SimpleXMLElement
    {
        if (is_object($this->_oPiXml)) {
            return $this->_oPiXml;
        }
        return NULL;
    }

    public function getPiGeneralArray(): array
    {
        $aGeneral = [];
        if (is_object($this->_oPiXml)) {
            $aGeneral = $this->_xml2Array($this->_oPiXml->general);
            $aDependencies = $this->_xml2Array($this->_oPiXml->dependencies);
            
            $aGeneral['dependencies'] = $aDependencies;
        }
        return $aGeneral;
    }

    /**
     * Returns generated list entry (li) for plugin or empty string
     * 
     * @param int $iPluginId
     * @return string
     */
    public function getInfoInstalled($iPluginId): string
    {
        $oPlugin = new pimPlugin($iPluginId);
        if ($oPlugin->isLoaded()) {
            $oView = new pimView();
            $oView->setMultiVariables($oPlugin->toArray());
            $aLang = array(
                'LANG_FOLDERNAME' => i18n("Foldername", "pluginmanager"),
                'LANG_AUTHOR' => i18n("Author", "pluginmanager"),
                'LANG_CONTACT' => i18n("Contact", "pluginmanager"),
                'LANG_LICENSE' => i18n("License", "pluginmanager"),
                'LANG_INSTALLED' => i18n('Installed since', 'pluginmanager'),
                'LANG_DEPENDENCIES' => i18n("Dependencies", "pluginmanager"),
                'LANG_WRITEABLE' => i18n("Writable", "pluginmanager"),
                'LANG_INSTALL' => i18n("Install", "pluginmanager"),
                'LANG_REMOVE' => i18n("Remove", "pluginmanager"),
                'LANG_UPDATE' => i18n('Update', 'pluginmanager'),
                'LANG_UPDATE_CHOOSE' => i18n('Please choose your new file', 'pluginmanager'),
                'LANG_UPDATE_UPLOAD' => i18n('Update', 'pluginmanager'),
                'LANG_REMOVE_SQL' => i18n('Execute uninstall.sql', 'pluginmanager')
            );
            $oView->setMultiVariables($aLang);
            // nav sub placeholder, @Todo: fill with content
            $oView->set('s', 'NAVSUB', '');
            // enable / disable functionality
            $activeStatus = $oPlugin->get('active');
            $oButton = new cHTMLButton('toggle_active');
            $oButton->setID("but-toggle-plugin-" . $oPlugin->get("idplugin"));
            $oButton->setClass("pimImgBut");
            $oButton->setMode('image');
            $oButtonLabel = new cHTMLLabel("placeholder", $oButton->getID());
            $oButtonLabel->setClass("pimButLabel");
            if ($activeStatus == 1) {
                $oButton->setAlt("Click to toggle status");
                $oButton->setImageSource('images/online.gif');
                $oButtonLabel->setLabelText(i18n("Plugin is active", "pluginmanager"));
            } else {
                $oButton->setImageSource('images/offline.gif');
                $oButtonLabel->setLabelText(i18n("Plugin not active", "pluginmanager"));
            }
            $oView->set('s', 'BUT_ACTIVESTATUS', $oButton->render() . '&nbsp;' . $oButtonLabel->render());

            // update button - not used right now
            $oView->set('s', 'BUT_UPDATE_PLUGIN', '');

            // uninstall
            $oDelBut = new cHTMLButton('uninstall_plugin');
            $oDelBut->setImageSource('images/but_cancel.gif');
            $oDelBut->setID("but-uninstall-plugin-" . $oPlugin->get("idplugin"));
            $oDelBut->setClass("pimImgBut");
            $oDelBut->setMode('image');
            $oDelSqlCheckbox = new cHTMLCheckbox("delete_sql", "TRUE");
            $oDelSqlCheckbox->setStyle("display: inline-block;");
            $sDelSqlTxt = " " . sprintf(i18n("(%s remove database tables)", "pluginmanager"), $oDelSqlCheckbox->toHtml(FALSE));
            $oDelButLabel = new cHTMLLabel("placeholder", $oDelBut->getID());
            $oDelButLabel->setClass("pimButLabel");
            $oDelButLabel->setLabelText(i18n("Uninstall Plugin", "pluginmanager") . $sDelSqlTxt);
            $oView->set('s', 'BUT_UNINSTALL_PLUGIN', $oDelBut->render() . '&nbsp;' . $oDelButLabel->render());

            $oView->setTemplate('pi_manager_installed_plugins.html');
            return $oView->getRendered(1);
        }
        return '';
    }

    protected function _getDepencyArray(): bool|array
    {
        $aDependencies = array();
        $aAttributes = array();
        $iCountDependencies = count($this->_oPiXml->dependencies);
        if($iCountDependencies > 0) {
            for ($i = 0; $i < $iCountDependencies; $i++) {
                $sPluginName = cSecurity::escapeString($this->_oPiXml->dependencies[$i]->depend);
                foreach ($this->_oPiXml->dependencies[$i]->depend->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = cSecurity::escapeString($sValue);
                }
                $aDependencies[$i]["name"] = $sPluginName;
                $aDependencies[$i] = array_merge($aDependencies[$i],$aAttributes);
            }
            return $aDependencies;
        }
        
        return false;
    }

    /**
     * 
     * @return boolean
     * @throws pimXmlStructureException
     */
    private function _validateXml(): bool
    {
        if ($this->_oDomDocument->schemaValidate($this->_xsd)) {
            $this->_bValid = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param ?SimpleXMLElement $xml
     * @return array
     */
    private function _xml2Array(?SimpleXMLElement $xml): array
    {
        if(empty($xml)) {
            return [];
        }
        $string = json_encode($xml);
        return json_decode($string, true);
    }

}
