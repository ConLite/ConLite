<?php

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class pimSetupPluginInstall extends pimSetupBase {

    const SQL_FILE = "plugin_install.sql";

    /**
     *
     * @var pimPlugin 
     */
    private $_oPlugin;
    //helper arrays
    private $_aAreas = array();
    private $_aInstalledAreas;
    private $_aInstalledNavMain;
    private $_aInstalledNavSub;

    public function __construct() {
        parent::__construct();
        $this->_initInstalledAreasArray();
    }

    public function installPlugin() {
        if (is_null($this->_oXml)) {
            cWarning(__FILE__, __LINE__, "installPlugin: No plugin xml loaded!");
            return 0;
        }
        $this->_installCheckUuid();
        $this->_installCheckRequirements();
        
        $oPiColl = new pimPluginCollection();
        $this->_oPlugin = $oPiColl->createNewItem();
        if ($this->_oPlugin->isLoaded()) {
            $this->_iPiId = $this->_oPlugin->get('idplugin');
            $this->_insertDbEntries();
            $this->_getPluginSql();
            if ($this->doQueries()) {
                $this->_oPlugin->set('idclient', $this->_iClient, FALSE);
                $this->_oPlugin->set('name', Contenido_Security::escapeDB(self::$XmlGeneral->plugin_name));
                $this->_oPlugin->set('description', Contenido_Security::escapeDB(self::$XmlGeneral->description));
                $this->_oPlugin->set('author', Contenido_Security::escapeDB(self::$XmlGeneral->author));
                $this->_oPlugin->set('copyright', Contenido_Security::escapeDB(self::$XmlGeneral->copyright));
                $this->_oPlugin->set('mail', Contenido_Security::escapeDB(self::$XmlGeneral->mail));
                $this->_oPlugin->set('website', Contenido_Security::escapeDB(self::$XmlGeneral->website));
                $this->_oPlugin->set('version', Contenido_Security::escapeDB(self::$XmlGeneral->version));
                $this->_oPlugin->set('folder', Contenido_Security::escapeDB(self::$XmlGeneral->plugin_foldername));
                $this->_oPlugin->set('uuid', Contenido_Security::escapeDB(self::$XmlGeneral->uuid));
                $this->_oPlugin->set('executionorder', $this->_updateSortOrder(), FALSE);
                $this->_oPlugin->set('installed', date('Y-m-d H:i:s'), FALSE);
                $this->_oPlugin->set('active', (int) self::$XmlGeneral['active'], FALSE);

                if ($this->_oPlugin->store()) {
                    //echo "stored: ".$this->_iPiId;
                    return $this->_iPiId;
                }
            } else {
                $this->_removeEmptyPlugin();
            }
        }
        // something went wrong, return 0
        return 0;
    }

    protected function _insertDbEntries() {
        $this->_addAreas();
        $this->_addActions();
        $this->_addFrames();
        $this->_addNavMain();
        $this->_addNavSub();
    }

    protected function _addRelation($iIdItem, $sType) {
        $oPluginRelation = $this->_PimPluginRelationCollection->createNewItem();
        $oPluginRelation->set('iditem', $iIdItem, FALSE);
        $oPluginRelation->set('idplugin', $this->_iPiId, FALSE);
        $oPluginRelation->set('type', $sType);
        $oPluginRelation->store();
        unset($oPluginRelation);
    }

    private function _addAreas() {
        $aAttributes = array();
        $aDefaultAttr = array(
            'menuless' => 0,
            'parent' => 0,
            'relevant' => 1
        );
        
        $iCountAreas = count(self::$XmlArea->area);
        if ($iCountAreas > 0) {
            $oAreaColl = new cApiAreaCollection();
            for ($i = 0; $i < $iCountAreas; $i++) {
                $sName = Contenido_Security::escapeDB(self::$XmlArea->area[$i], $this->oDb);
                // build attributes
                foreach (self::$XmlArea->area[$i]->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = (string) $sValue;
                }
                $aAttributes = array_merge($aDefaultAttr, $aAttributes);
                /* @var $oArea cApiArea */
                $oArea = $oAreaColl->createNewItem($this->_getNextId("area"));
                $oArea->set('parent_id', Contenido_Security::escapeDB($aAttributes['parent'], $this->oDb));
                $oArea->set('name', $sName);
                $oArea->set('menuless', Contenido_Security::toInteger($aAttributes['menuless']));
                $oArea->set('relevant', 1, FALSE);
                $oArea->set('online', 1, FALSE);
                if ($oArea->store()) {
                    $iIdItem = $oArea->get($oArea->primaryKey);
                    $this->_aAreas[$sName] = $iIdItem;
                    $this->_aInstalledAreas[] = $sName;
                    $this->_addRelation($iIdItem, 'area');
                }
            }
        }
    }

    private function _addActions() {
        $aAttributes = array();
        $aDefaultAttr = array(
            'relevant' => 1
        );

        $iCountActions = count(self::$XmlActions->action);
        if ($iCountActions > 0) {
            $oActionColl = new cApiActionCollection();
            for ($i = 0; $i < $iCountActions; $i++) {
                $sName = Contenido_Security::escapeDB(self::$XmlActions->action[$i], $this->_oDb);
                foreach (self::$XmlActions->action[$i]->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = cSecurity::escapeString($sValue);
                }
                $aAttributes = array_merge($aDefaultAttr, array_filter($aAttributes));
                if (!in_array($aAttributes['area'], $this->_aInstalledAreas)) {
                    parent::error(sprintf(i18n('Defined area <strong>%s</strong> are not found on your ConLite installation. Please contact your plugin author.', 'pluginmanager'), $aAttributes['area']));
                }
                /* @var $oAction cApiAction */
                $oAction = $oActionColl->createNewItem($this->_getNextId("actions"));
                if ($oAction->isLoaded()) {
                    $oAction->set("idarea", $this->_getIdForArea($aAttributes['area']));
                    $oAction->set("name", $sName);
                    $oAction->set("code", '');
                    $oAction->set("location", '');
                    $oAction->set("relevant", (int) $aAttributes['relevant']);
                    if ($oAction->store()) {
                        $this->_addRelation($oAction->get('idaction'), 'actions');
                    }
                }
                //$oAction = $oActionColl->create($aAttributes['area'], $sName, '', '', $aAttributes['relevant']);
                //$this->_addRelation($oAction->get('idaction'), 'actions');
            }
            unset($oActionColl);
            unset($oAction);
        }
    }

    private function _addFrames() {
        $aAttributes = array();
        $aDefaultAttr = array();

        $iCountFrames = count(self::$XmlFrames->frame);
        if ($iCountFrames > 0) {
            $oFrameFileColl = new cApiFrameFileCollection();
            $oFileColl = new cApiFileCollection();

            for ($i = 0; $i < $iCountFrames; $i++) {
                // Build attributes with security checks
                foreach (self::$XmlFrames->frame[$i]->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = cSecurity::escapeString($sValue);
                }
                $aAttributes = array_merge($aDefaultAttr, array_filter($aAttributes));

                // Check for valid area
                if (!array_key_exists($aAttributes['area'], $this->_aAreas) && !in_array($aAttributes['area'], $this->_aInstalledAreas)) {
                    parent::error(sprintf(i18n('Defined area <strong>%s</strong> are not found on your ConLite installation. Please contact your plugin author.', 'pluginmanager'), $aAttributes['area']));
                }

                /* @var $oFile cApiFile */
                $oFile = $oFileColl->createNewItem($this->_getNextId("files"));
                if ($oFile->isLoaded()) {
                    $this->_addRelation($oFile->get('idfile'), 'files');
                    
                    $oFile->set("idarea", $this->_getIdForArea($aAttributes['area']));
                    $oFile->set("filename", $aAttributes['name']);
                    $oFile->set("filetype", cSecurity::escapeString($aAttributes['filetype']));

                    if ($oFile->store()) {
                        if (!empty($aAttributes['frameId'])) {
                            /* @var $oFrameFile cApiFrameFile */
                            $oFrameFile = $oFrameFileColl->createNewItem($this->_getNextId("framefiles"));
                            if ($oFrameFile->isLoaded()) {
                                $this->_addRelation($oFrameFile->get('idframefile'), 'framefiles');
                                
                                $oFrameFile->set("idarea", $this->_getIdForArea($aAttributes['area']));
                                $oFrameFile->set("idframe", (int) $aAttributes['frameId'], FALSE);
                                $oFrameFile->set("idfile", (int) $oFile->get('idfile'), FALSE);
                                
                                $oFrameFile->store();
                            }
                        }
                    }
                }
            }
            unset($oFrameFileColl);
            unset($oFileColl);
            unset($oFile);
            unset($oFrameFile);
        }
    }

    private function _addNavMain() {
        $aAttributes = array();

        $iCountNavMain = count(self::$XmlNavMain->nav);
        if ($iCountNavMain > 0) {
            $oNavMainColl = new cApiNavMainCollection();

            for ($i = 0; $i < $iCountNavMain; $i++) {
                $sLocation = cSecurity::escapeString(self::$XmlNavMain->nav[$i]);
                if (empty($sLocation)) {
                    parent::error(i18n('There seem to be an empty main navigation entry in plugin.xml. Please contact your plugin author.', 'pluginmanager'), $this->_iPiId);
                }

                // Build attributes with security checks
                foreach (self::$XmlNavMain->nav[$i]->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = cSecurity::escapeString($sValue);
                }

                /* @var $oNavMain cApiNavMain */
                $oNavMain = $oNavMainColl->createNewItem($this->_getNextId("nav_main"));
                if($oNavMain->isLoaded()) {
                    $this->_addRelation($oNavMain->get('idnavm'), 'nav_main');
                    
                    $oNavMain->set("location", $sLocation, FALSE);
                    $oNavMain->set("name", cSecurity::escapeString($aAttributes['name']));
                    
                    $oNavMain->store();
                }
            }
            unset($oNavMainColl);
            unset($oNavMain);
        }
    }

    private function _addNavSub() {
        $aAttributes = array();
        $this->_initInstalledNavMainArray();
        $iCountNavSub = count(self::$XmlNavSub->nav);
        
        if ($iCountNavSub > 0) {
            $oNavSubColl = new cApiNavSubCollection();
            
            for ($i = 0; $i < $iCountNavSub; $i++) {
                $sLocation = cSecurity::escapeString(self::$XmlNavSub->nav[$i]);
                
                if (empty($sLocation)) {
                    parent::error(i18n('There seem to be an empty sub navigation entry in plugin.xml. Please contact your plugin author.', 'pluginmanager'), $this->_iPiId);
                }
                
                // Build attributes with security checks
                foreach (self::$XmlNavSub->nav[$i]->attributes() as $sKey => $sValue) {
                    $aAttributes[$sKey] = cSecurity::escapeString($sValue);
                }
                /* @var $oNavSub cApiNavSub */
                $oNavSub = $oNavSubColl->createNewItem($this->_getNextId("nav_sub"));
                if ($oNavSub->isLoaded()) {
                    $this->_addRelation($oNavSub->get('idnavs'), 'nav_sub');
                    
                    $oNavSub->set("idnavm", $this->_getIdForNavMain($aAttributes['navm']));
                    $oNavSub->set("idarea", $this->_getIdForArea($aAttributes['area']));
                    $oNavSub->set("level", (int) $aAttributes['level']);
                    $oNavSub->set("location", $sLocation, FALSE);
                    $oNavSub->set("online", 1, FALSE);
                    
                    $oNavSub->store();
                }
            }
            unset($oNavSubColl);
            unset($oNavSub);
        }        
    }

    private function _removeEmptyPlugin() {
        if (empty($this->_iPiId)) {
            return FALSE;
        }
        $this->_getRelations();
        if (count($this->_aRelations) > 0) {
            $this->_deleteRelationEntries();
            $this->_deleteRelations();
        }
        $this->_PimPluginCollection->delete($this->_iPiId);
    }

    /**
     * Check uuId: You can install a plugin only for one time
     */
    private function _installCheckUuid() {
        $this->_PimPluginCollection->setWhere('uuid', self::$XmlGeneral->uuid);
        $this->_PimPluginCollection->query();
        if ($this->_PimPluginCollection->count() > 0) {
            parent::error(i18n('You can install this plugin only for one time.', 'pluginmanager'));
        }
    }
    
    private function _installCheckRequirements() {

        // Check min ConLite version
        if (version_compare(CL_VERSION, self::$XmlRequirements->conlite->attributes()->minversion, '<')) {
            parent::error(sprintf(i18n('You have to install ConLite <strong>%s</strong> or higher to install this plugin!', 'pluginmanager'), self::$XmlRequirements->conlite->attributes()->minversion));
        }
        
        // Check max ConLite version
        if (self::$XmlRequirements->conlite->attributes()->maxversion) {
            if (version_compare(CL_VERSION, self::$XmlRequirements->conlite->attributes()->maxversion, '>')) {
                parent::error(sprintf(i18n('Your current ConLite version is to new - max ConLite version %s', 'pluginmanager'), self::$XmlRequirements->conlite->attributes()->maxversion));
            }
        }

        // Check PHP version
        if (version_compare(phpversion(), self::$XmlRequirements->attributes()->php, '<')) {
            parent::error(sprintf(i18n('You have to install PHP <strong>%s</strong> or higher to install this plugin!', 'pluginmanager'), self::$XmlRequirements->attributes()->php));
        }
        
        /* @todo check and implement other requirement checks
        // Check extensions
        if (count(parent::$XmlRequirements->extension) != 0) {

            for ($i = 0; $i < count(parent::$XmlRequirements->extension); $i++) {

                if (!extension_loaded(parent::$XmlRequirements->extension[$i]->attributes()->name)) {
                    parent::error(sprintf(i18n('The plugin could not find the PHP extension <strong>%s</strong>. Because this is required by the plugin, it can not be installed.', 'pim'), parent::$XmlRequirements->extension[$i]->attributes()->name));
                }
            }
        }

        // Check classes
        if (count(parent::$XmlRequirements->class) != 0) {

            for ($i = 0; $i < count(parent::$XmlRequirements->class); $i++) {

                if (!class_exists(parent::$XmlRequirements->class[$i]->attributes()->name)) {
                    parent::error(sprintf(i18n('The plugin could not find the class <strong>%s</strong>. Because this is required by the plugin, it can not be installed.', 'pim'), parent::$XmlRequirements->class[$i]->attributes()->name));
                }
            }
        }

        // Check functions
        if (count(parent::$XmlRequirements->function) != 0) {

            for ($i = 0; $i < count(parent::$XmlRequirements->function); $i++) {

                if (!function_exists(parent::$XmlRequirements->function[$i]->attributes()->name)) {
                    parent::error(sprintf(i18n('The plugin could not find the function <strong>%s</strong>. Because this is required by the plugin, it can not be installed.', 'pim'), parent::$XmlRequirements->function[$i]->attributes()->name));
                }
            }
        }
         * 
         */
    }

    private function _initInstalledAreasArray() {
        $this->_aInstalledAreas = array();
        $oAreaColl = new cApiAreaCollection();
        $oAreaColl->select();
        //$oAreaColl->query();
        /* @var $oArea cApiArea */
        while ($oArea = $oAreaColl->next()) {
            $this->_aInstalledAreas[] = $oArea->get('name');
        }
        //print_r($this->_aInstalledAreas);
    }

    private function _initInstalledNavMainArray() {
        $this->_aInstalledNavMain = array();
        $oNavMainColl = new cApiNavMainCollection();
        $oNavMainColl->select();
        //$oNavMainColl->query();
        /* @var $oArea cApiArea */
        while ($oNavMain = $oNavMainColl->next()) {
            $this->_aInstalledNavMain[$oNavMain->get('name')] = $oNavMain->get('idnavm');
        }
    }

    private function _initInstalledNavSubArray() {
        $this->_aInstalledNavSub = array();
        $oNavSubColl = new cApiNavSubCollection();
        $oNavMainColl->select();
        //$oNavSubColl->query();
        /* @var $oArea cApiArea */
        while ($oNavSub = $oNavSubColl->next()) {
            $this->_aInstalledNavMain[$oNavSub->get('idnavsub')] = $oNavSub->get('name');
        }
    }

    private function _getIdForArea($sArea) {
        if (array_key_exists($sArea, $this->_aAreas)) {
            return $this->_aAreas[$sArea];
        }

        if (in_array($sArea, $this->_aInstalledAreas)) {
            $oArea = new cApiArea();
            $oArea->loadBy("name", $sArea);
            if ($oArea->isLoaded()) {
                return $oArea->get($oArea->primaryKey);
            }
        }
        parent::error(sprintf(i18n('Defined area <strong>%s</strong> not found on your ConLite installation. Please contact your plugin author.', "pluginmanager"), $sArea), $this->_iPiId);
    }
    
    private function _getIdForNavMain($sNavMain) {
        if($sNavMain == "0") {
            return $sNavMain;
        }
        if (array_key_exists($sNavMain, $this->_aInstalledNavMain)) {
            return $this->_aInstalledNavMain[$sNavMain];
        }
        parent::error(sprintf(i18n('Defined nav main <strong>%s</strong> not found on your ConLite installation. Please contact your plugin author.', "pluginmanager"), $sNavMain), $this->_iPiId);
    }

    public function __destruct() {
        //echo "<pre>";
        //print_r($this->_aAreas);
    }

}
