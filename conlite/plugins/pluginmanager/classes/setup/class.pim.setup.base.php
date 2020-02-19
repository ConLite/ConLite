<?php

/**
 * 
 */
class pimSetupBase {

    /**
     * name of plugin info xml file
     */
    const PIM_XML_FILENAME = "cl_plugin.xml";

    /**
     * placeholder for sql prefix
     */
    const PIM_SQL_PLACEHOLDER = "!PREFIX!";

    /**
     *
     * @var int mode of setup 
     */
    public static $mode;

    /**
     * plugin general infos
     * @var SimpleXMLElement 
     */
    public static $XmlGeneral;

    /**
     * plugin requirements
     * @var SimpleXMLElement
     */
    public static $XmlRequirements;

    /**
     * plugin depencies
     * @var SimpleXMLElement
     */
    public static $XmlDependencies;

    /**
     * areas for plugin
     * @var SimpleXMLElement
     */
    public static $XmlArea;

    /**
     * actions for plugin
     * @var SimpleXMLElement
     */
    public static $XmlActions;

    /**
     * frames for plugin - frame and framefile entries
     * @var SimpleXMLElement
     */
    public static $XmlFrames;

    /**
     * nav main entry
     * @var SimpleXMLElement
     */
    public static $XmlNavMain;

    /**
     * nav sub entries
     * @var SimpleXMLElement
     */
    public static $XmlNavSub;

    /**
     * content type(s) for plugin
     * @var SimpleXMLElement
     */
    public static $XmlContentType;

    /**
     * whole xml object from info xml
     * @var SimpleXMLElement
     */
    protected $_oXml = NULL;
    protected $_xmlDefault = 'plugins/pluginmanager/xml/plugin_default.xml';
    protected $_aXmlDefaultIndex;
    protected $_aXmlDefaultValues;

    /**
     *
     * @var string 
     */
    protected $_sXsdPath;

    /**
     * db-tables and table-ids used 
     * @var array
     */
    protected $_aTables = array(
        'actions' => 'idaction',
        'area' => 'idarea',
        'files' => 'idfile',
        'framefiles' => 'idframefile',
        'nav_main' => 'idnavm',
        'nav_sub' => 'idnavs',
        'plugins' => 'idplugin'
    );

    /**
     * holds db object
     * @var DB_ConLite 
     */
    protected $_oDb;
    protected $_aSql = array();
    protected $_iPiId = 0;
    protected $_iCntQueries = 0;
    protected $_iClient;
    protected $_aRelations;

    /**
     *
     * @var pimPluginCollection 
     */
    protected $_PimPluginCollection;

    /**
     *
     * @var pimPluginRelationCollection
     */
    protected $_PimPluginRelationCollection;
    protected $_sPluginPath;

    public function __construct() {
        $this->_oDb = new DB_ConLite();
        $this->_iClient = cRegistry::getClientId();
        $this->_xmlParseIntoStruct();

        $this->_PimPluginCollection = new pimPluginCollection();
        $this->_PimPluginRelationCollection = new pimPluginRelationCollection();

        //print_r($this->_getAttrForTag("area"));
    }

    public function setPluginPath($sPath) {
        $this->_sPluginPath = $sPath;
    }

    public function getPluginPath() {
        return $this->_sPluginPath;
    }

    /**
     * 
     * @return boolean
     */
    public function doQueries() {
        if (!is_array($this->_aSql) || count($this->_aSql) <= 0) {
            return TRUE;
        }
        $iQueries = count($this->_aSql);

        if ($iQueries > 0 && is_a($this->_oDb, "DB_ConLite")) {
            foreach ($this->_aSql as $sSql) {
                try {
                    $this->_oDb->query($sSql);
                } catch (Exception $exc) {
                    self::error($exc->getTraceAsString());
                }
                $this->_iCntQueries++;
            }
            if ($iQueries == $this->_iCntQueries) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function undoQueries() {
        
    }

    public function setXmlObject($oXml, $bSplit = TRUE) {
        if (is_object($oXml)) {
            $this->_oXml = & $oXml;
        }

        if ($bSplit) {
            $this->_setXml();
        }
    }

    public function setXsdFile($sFile) {
        $this->_sXsdPath = $sFile;
    }

    /**
     * Returns next id for given table
     * 
     * @param string $sTable
     * @return int the next usable table id
     */
    protected function _getNextId($sTable) {
        cInclude("includes", "functions.database.php");
        dbUpdateSequence(cRegistry::getConfigValue("tab", "sequence"), cRegistry::getConfigValue('tab', $sTable), cRegistry::getDb());

        $iNextId = $this->_oDb->nextid(cRegistry::getConfigValue('tab', $sTable));
        // id must be over 10.000
        if ($iNextId < 10000) {
            $iNextId = 10000;
        }

        // added ten
        $iNextId = $iNextId + 10;

        // how long is the number?
        $iResultStrlen = strlen($iNextId);

        // removed the last number
        $iNextId = substr($iNextId, 0, $iResultStrlen - 1);

        return Contenido_Security::toInteger($iNextId . 0); // last number is always a zero
    }

    protected function _getAttrForTag($sTag) {
        foreach ($this->_aXmlDefaultValues as $Key => $aValue) {
            if ($aValue['tag'] === strtoupper($sTag) && $aValue['type'] === "complete") {
                if (isset($aValue['attributes']) && is_array($aValue['attributes'])) {
                    return array_change_key_case($aValue['attributes']);
                }
            }
        }
        return FALSE;
    }

    protected function _getPluginSql() {
        $sSqlFile = $this->_sPluginPath . static::SQL_FILE;
        if (file_exists($sSqlFile) && is_readable($sSqlFile)) {
            $this->_aSql = pimSqlParser::parse(file_get_contents($sSqlFile));
        } else if (!is_array($this->_aSql)) {
            $this->_aSql = array();
        }
    }

    protected static function error($sMessage, $iPiId = NULL) {
        if (!is_null($iPiId)) {
            $oUnInstall = new pimSetupPluginUninstall();
            $oUnInstall->uninstallPlugin($iPiId, '');
        }
        print "Error:0:" . $sMessage;
        die();
    }

    protected function _getRelations() {
        $aTmpArray = array();
        $this->_PimPluginRelationCollection->setWhere('idplugin', $this->_iPiId);
        $this->_PimPluginRelationCollection->query();
        if ($this->_PimPluginRelationCollection->count() > 0) {
            while ($oPluginRelation = $this->_PimPluginRelationCollection->next()) {
                if (isset($aTmpArray[$oPluginRelation->get('type')]) && is_array($aTmpArray[$oPluginRelation->get('type')])) {
                    array_push($aTmpArray[$oPluginRelation->get('type')], $oPluginRelation->get('iditem'));
                } else {
                    $aTmpArray[$oPluginRelation->get('type')] = array($oPluginRelation->get('iditem'));
                }
            }
            $this->_aRelations = $aTmpArray;
            unset($aTmpArray);
        }
    }

    protected function _deleteRelations() {
        $iDeletetRelations = $this->_PimPluginRelationCollection->deleteByWhereClause("idplugin = " . $this->_iPiId);
    }

    protected function _deleteRelationEntries() {
        $oDb = new DB_ConLite();
        foreach ($this->_aRelations as $sType => $aIds) {
            $sSQL = 'DELETE FROM ' . cRegistry::getConfigValue('tab', $sType) . ' WHERE ' . $this->_aTables[$sType] . ' IN (' . implode(',', $aIds) . ')';
            if ($oDb->query($sSQL) == FALSE) {
                return FALSE;
            }
        }
        unset($oDb);
        return TRUE;
    }

    protected function _updateSortOrder() {
        if(!isset($_REQUEST['new_position'])) {
            return 0;
        }
        
        $oPluginColl = new pimPluginCollection();
        $oPluginColl->setWhere("executionorder", (int) $_REQUEST['new_position'], ">=");
        $oPluginColl->query();
        if($oPluginColl->count() > 0) {
            /* @var $oPlugin cApiPlugin */
            while ($oPlugin = $oPluginColl->next()) {
                $iOrder = $oPlugin->get("executionorder");
                $oPlugin->set("executionorder", $iOrder++);
                $oPlugin->store();
            }
        }
        return (int) $_REQUEST['new_position'];
    }

    /**
     * Set temporary xml content to static variables
     *
     * @param string $xml
     */
    private function _setXml() {
        //simplexml_tree($this->_oXml);
        // General plugin informations
        self::$XmlGeneral = $this->_oXml->general;

        // Plugin requirements
        self::$XmlRequirements = $this->_oXml->requirements;

        // Plugin dependencies
        self::$XmlDependencies = $this->_oXml->dependencies;

        // CONTENIDO areas: *_area
        self::$XmlArea = $this->_oXml->conlite->areas;

        // CONTENIDO actions: *_actions
        self::$XmlActions = $this->_oXml->conlite->actions;

        // CONTENIDO frames: *_frame_files and *_files
        self::$XmlFrames = $this->_oXml->conlite->frames;

        // CONTENIDO main navigations: *_nav_main
        self::$XmlNavMain = $this->_oXml->conlite->nav_main;

        // CONTENIDO sub navigations: *_nav_sub
        self::$XmlNavSub = $this->_oXml->conlite->nav_sub;

        // CONTENIDO Content Types: *_type
        self::$XmlContentType = $this->_oXml->content_types;
    }

    private function _xmlParseIntoStruct() {
        $sData = implode("", file($this->_xmlDefault));
        $oParser = xml_parser_create();
        xml_parse_into_struct($oParser, $sData, $this->_aXmlDefaultValues, $this->_aXmlDefaultIndex);
        xml_parser_free($oParser);
    }

    private function _updateSequence($table = false) {
        global $db, $cfg;
        if (!$table) {
            $sql = "SHOW TABLES";
            $db->query($sql);
            while ($db->next_record()) {
                dbUpdateSequence($cfg['sql']['sqlprefix'] . "_sequence", $db->f(0));
            }
        } else {
            dbUpdateSequence($cfg['sql']['sqlprefix'] . "_sequence", $table);
        }
    }
}
