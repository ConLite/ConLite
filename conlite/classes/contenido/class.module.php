<?php

/**
 * File:
 * class.module.php
 *
 * Description:
 *  cApi class
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

use ConLite\Log\LogWriter;
use ConLite\Log\Log;

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

cInclude('includes', 'functions.upl.php');

class cApiModuleCollection extends ItemCollection {

    /**
     * Constructor Function
     * @param none
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["mod"], "idmod");
        $this->_setItemClass("cApiModule");
    }

    /**
     * Creates a new communication item
     */
    public function create($name) {
        global $auth, $client;
        $item = parent::createNewItem();

        $item->set("idclient", $client);
        $item->set("name", $name);
        $item->set("author", $auth->auth["uid"]);
        $item->set("created", date("Y-m-d H:i:s"), false);
        $item->store();
        return $item;
    }

    public function delete($iIdMod) {
        /* @var $oMod cApiModule */
        $oMod = $this->_itemClassInstance;
        $oMod->_bNoted = TRUE;
        $oMod->loadByPrimaryKey($iIdMod);
        if ($oMod->isLoaded() && $oMod->hasModuleFolder()) {
            $oMod->deleteModuleFolder();
        }
        unset($oMod);
        return parent::delete($iIdMod);
    }

}

/**
 * Module access class
 */
class cApiModule extends Item {

    public $_oldumask;
    /**
     * @var mixed
     */
    public $_sModAliasOld;
    public $_bNoted;
    protected $_error;

    /**
     * Assoziative package structure array
     * @var array
     */
    protected $_packageStructure;
    protected $_bOutputFromFile = false;
    protected $_bInputFromFile = false;

    private array $aUsedTemplates = [];

    /**
     * Configuration Array of ModFileEdit
     * array is set with entries in local config file
     * 
     * @var array 
     */
    private $_aModFileEditConf = ['use' => false, 'modFolderName' => 'data/modules'];

    private ?string $_sModAlias = null;

    private ?string $_sModPath = null;
    private array $_aModDefaultStruct = ['css', 'js', 'php', 'template', 'image', 'lang', 'xml'];

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        $cfg = cRegistry::getConfig();
        $cfgClient = cRegistry::getClientConfig(cRegistry::getClientId());
        
        parent::__construct($cfg["tab"]["mod"], "idmod");

        // Using no filters is just for compatibility reasons.
        // That's why you don't have to stripslashes values if you store them
        // using ->set. You have to add slashes, if you store data directly
        // (data not from a form field)
        $this->setFilters([], []);

        $this->_packageStructure = ["jsfiles" => $cfgClient["js"]["path"], "tplfiles" => $cfgClient["tpl"]["path"], "cssfiles" => $cfgClient["css"]["path"]];

        if (isset($cfg['dceModEdit']) && is_array($cfg['dceModEdit'])) {
            $this->_aModFileEditConf['clientPath'] = $cfgClient["path"]["frontend"];
            $this->_aModFileEditConf = array_merge($this->_aModFileEditConf, $cfg['dceModEdit']);
            if (!isset($cfg['dceModEdit']['modPath']) || empty($cfg['dceModEdit']['modPath'])) {
                $this->_aModFileEditConf['modPath'] = $cfgClient["path"]["frontend"]
                        . $this->_aModFileEditConf['modFolderName'] . "/";
            }
        }

        $cApiClient = new cApiClient(cRegistry::getClientId());
        $aClientProp = $cApiClient->getPropertiesByType('modfileedit');
        if ($aClientProp !== []) {
            $this->_aModFileEditConf = array_merge($this->_aModFileEditConf, $aClientProp);
        }

        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    public function createModuleFolder() {
        //echo $this->_aModFileEditConf['modPath'];
        $sPathErrorLog = cRegistry::getConfigValue('path', 'logs').'errorlog.txt';
        
        if (is_writable($this->_aModFileEditConf['clientPath']) && !file_exists($this->_aModFileEditConf['modPath'])) {
            try {
                mkdir($this->_aModFileEditConf['modPath'], 0777, true);
            } catch (Exception $ex) {
                $writer = LogWriter::factory("File", ['destination' => $sPathErrorLog]);
                $log = new Log($writer);
                $log->log($ex->getFile() . " (" . $ex->getLine() . "): " . $ex->getMessage(), Log::WARN);
            }
        }

        if ($this->_aModFileEditConf['use'] == true && is_writable($this->_aModFileEditConf['modPath'])) {
            if (!is_dir($this->getModulePath())) {
                $this->_oldumask = umask(0);
                try {
                    mkdir($this->getModulePath(), 0777);
                } catch (Exception $ex) {
                    $writer = LogWriter::factory("File", ['destination' => $sPathErrorLog]);
                    $log = new Log($writer);
                    $log->log($ex->getFile() . " (" . $ex->getLine() . "): " . $ex->getMessage(), Log::WARN);
                }

                if (is_writable($this->getModulePath())) {
                    return $this->_createModuleStruct();
                }
                umask($this->_oldumask);
            }
        } else {
            $writer = LogWriter::factory("File", ['destination' => $sPathErrorLog]);
            $log = new Log($writer);
            $log->log(__FILE__ . " (" . __LINE__ . "): " . 'Error: Cannot create mod path '.$this->getModulePath(), Log::WARN);
        }
        return FALSE;
    }

    public function deleteModuleFolder() {
        if ($this->_aModFileEditConf['use'] == TRUE && is_writable($this->_aModFileEditConf['modPath']) && is_dir($this->getModulePath())) {
            return $this->_recursiveRemoveDirectory($this->getModulePath());
        }
        return FALSE;
    }

    public function getModuleAlias() {
        return $this->_sModAlias;
    }

    public function hasModuleFolder() {
        $sOldPath = $this->_aModFileEditConf['modPath'] . uplCreateFriendlyName($this->get("name"));
        return (file_exists($this->getModulePath()) || file_exists($sOldPath));
    }

    public function getModulePath() {
        if (empty($this->_sModPath)) {
            $this->_setModulePath();
        }
        return $this->_sModPath;
    }

    protected function _setModulePath() {
        $this->_sModPath = $this->_aModFileEditConf['modPath'] . $this->getModuleAlias() . "/";
    }

    /**
     * Returns the translated name of the module if a translation exists.
     *
     * @param none
     * @return string Translated module name or original
     */
    public function getTranslatedName() {
        global $lang;

        // If we're not loaded, return
        if ($this->virgin == true) {
            return false;
        }

        $modname = $this->getProperty("translated-name", $lang);

        if ($modname === false) {
            return $this->get("name");
        } else {
            return $modname;
        }
    }

    /**
     * Sets the translated name of the module
     *
     * @param $name string Translated name of the module
     * @return none
     */
    public function setTranslatedName($name) {
        global $lang;
        $this->setProperty("translated-name", $lang, $name);
    }

    /**
     * Parses the module for mi18n strings and returns them in an array
     *
     * @return array Found strings for this module
     */
    public function parseModuleForStrings(): bool|array
    {
        global $cfg;
        if ($this->virgin == true) {
            return false;
        }

        // Fetch the code, append input to output
        $code = $this->get("output");
        $code .= $this->get("input");

        // Initialize array
        $strings = [];

        // Split the code into mi18n chunks
        $varr = preg_split('/mi18n([\s]*)\(([\s]*)"/', $code, -1);

        if ((is_countable($varr) ? count($varr) : 0) > 1) {
            foreach ($varr as $key => $value) {
                // Search first closing
                $closing = strpos($value, '")');

                if ($closing === false) {
                    $closing = strpos($value, '" )');
                }

                if ($closing !== false) {
                    $value = substr($value, 0, $closing) . '")';
                }

                // Append mi18n again
                $varr[$key] = 'mi18n("' . $value;

                // Parse for the mi18n stuff
                preg_match_all('/mi18n([\s]*)\("(.*)"\)/', $varr[$key], $results);

                // Append to strings array if there are any results
                if (is_array($results[1]) && (is_countable($results[2]) ? count($results[2]) : 0) > 0) {
                    $strings = array_merge($strings, $results[2]);
                }

                // Unset the results for the next run
                unset($results);
            }
        }

        // adding dynamically new module translations by content types
        // this function was introduced with contenido 4.8.13
        // checking if array is set to prevent crashing the module translation page
        if (is_array($cfg['translatable_content_types']) && $cfg['translatable_content_types'] !== []) {
            // iterate over all defines cms content types
            foreach ($cfg['translatable_content_types'] as $sContentType) {
                // check if the content type exists and include his class file
                if (file_exists($cfg['contenido']['path'] . "classes/class." . strtolower($sContentType) . ".php")) {
                    cInclude("classes", "class." . strtolower($sContentType) . ".php");
                    // if the class exists, has the method "addModuleTranslations"
                    // and the current module contains this cms content type we
                    // add the additional translations for the module
                    if (class_exists($sContentType) && method_exists($sContentType, 'addModuleTranslations') && preg_match('/' . strtoupper($sContentType) . '\[\d+\]/', $code)) {

                        $strings = call_user_func([$sContentType, 'addModuleTranslations'], $strings);
                    }
                }
            }
        }

        // Make the strings unique
        return array_unique($strings);
    }

    /**
     * Checks if the module is in use
     * @return bool    Specifies if the module is in use
     */
    public function moduleInUse($module, $bSetData = false) {
        global $cfg;

        $dbConLite = new DB_ConLite();

        $sql = "SELECT
                    c.idmod, c.idtpl, t.name
                FROM
                " . $cfg["tab"]["container"] . " as c,
                " . $cfg["tab"]["tpl"] . " as t
                WHERE
                    c.idmod = '" . Contenido_Security::toInteger($module) . "' AND
                    t.idtpl=c.idtpl
                GROUP BY c.idtpl
                ORDER BY t.name";
        $dbConLite->query($sql);

        if ($dbConLite->nf() == 0) {
            return false;
        } else {
            $i = 0;
            // save the datas of used templates in array
            if ($bSetData === true) {
                while ($dbConLite->next_record()) {
                    $this->aUsedTemplates[$i]['tpl_name'] = $dbConLite->f('name');
                    $this->aUsedTemplates[$i]['tpl_id'] = (int) $dbConLite->f('idmod');
                    $i++;
                }
            }

            return true;
        }
    }

    /**
     * Get the informations of used templates
     * @return array template data
     */
    public function getUsedTemplates() {
        return $this->aUsedTemplates;
    }

    /**
     * Checks if the module is a pre-4.3 module
     * @return boolean true if this module is an old one
     * 
     * @deprecated since version 2.0
     */
    public function isOldModule() {
        // Keywords to scan
        $scanKeywords = ['$cfgTab', 'idside', 'idsidelang'];

        $input = $this->get("input");
        $output = $this->get("output");

        foreach ($scanKeywords as $scanKeyword) {
            if (strstr($input, $scanKeyword)) {
                return true;
            }
            if (strstr($output, $scanKeyword)) {
                return true;
            }
        }
    }

    public function getField($field) {
        $value = parent::getField($field);

        if ($field === "name" && $value == "") {
            $value = i18n("- Unnamed Module -");
        }
        return ($value);
    }

    public function store($bJustStore = false) {
        global $cfg;
        /* dceModFileEdit (c)2009-2011 www.dceonline.de */
        if ($this->_aModFileEditConf['use'] == true && ($this->_aModFileEditConf['allModsFromFile'] == true || (is_array($this->_aModFileEditConf['modsFromFile']) && in_array($this->get('idmod'), $this->_aModFileEditConf['modsFromFile'])))) {
            $this->modifiedValues['output'] = true;
            $this->modifiedValues['input'] = true;
        }
        /* End dceModFileEdit (c)2009-2011 www.dceonline.de */
        if ($bJustStore) {
            // Just store changes, e.g. if specifying the mod package
            parent::store();
        } else {
            cInclude("includes", "functions.con.php");

            parent::store();

            conGenerateCodeForAllArtsUsingMod($this->get("idmod"));

            if ($this->_shouldStoreToFile() && $this->_makeFileDirectoryStructure()) {
                $sRootPath = $cfg['path']['contenido'] . $cfg['path']['modules'] . $this->get("idclient") . "/";
                file_put_contents($sRootPath . $this->get("idmod") . ".xml", $this->export($this->get("idmod") . ".xml", true));
            }
        }
    }

    public function getModFileEditConf() {
        return $this->_aModFileEditConf;
    }

    protected function _recursiveRemoveDirectory($directory): bool {
        foreach (glob("{$directory}/*") as $file) {
            if (is_dir($file)) {
                $this->_recursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }
        return rmdir($directory);
    }

    protected function _makeFileDirectoryStructure() {
        global $cfg;

        $sRootPath = $cfg['path']['contenido'] . $cfg['path']['modules'];
        if (!is_dir($sRootPath)) {
            @mkdir($sRootPath);
        }

        $sRootPath = $cfg['path']['contenido'] . $cfg['path']['modules'] . $this->get("idclient") . "/";
        if (!is_dir($sRootPath)) {
            @mkdir($sRootPath);
        }

        if (is_dir($sRootPath)) {
            return true;
        } else {
            return false;
        }
    }

    protected function _shouldStoreToFile() {
        if (getSystemProperty("modules", "storeasfiles") == "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    protected function _shouldLoadFromFiles() {
        if (getSystemProperty("modules", "loadfromfiles") == "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Parse import xml file, stores data in global variable (-> event handler functions)
     *
     * @param string $sFile Filename including path of import xml file
     * @param string $sType Import type, "module" or "package"
     * @return bool Returns true, if file has been parsed
     */
    private function _parseImportFile($sFile, $sType = "module", $sEncoding = "ISO-8859-1") {
        global $_mImport;

        $clXmlParser = new clXmlParser($sEncoding);

        if ($sType == "module") {
            $clXmlParser->setEventHandlers(["/module/name" => "cHandler_ModuleData", "/module/description" => "cHandler_ModuleData", "/module/type" => "cHandler_ModuleData", "/module/input" => "cHandler_ModuleData", "/module/output" => "cHandler_ModuleData"]);
        } else {
            $aHandler = [
                "/modulepackage/guid" => "cHandler_ModuleData",
                #"/modulepackage/repository_guid"    => "cHandler_ModuleData",
                "/modulepackage/module/name" => "cHandler_ModuleData",
                "/modulepackage/module/description" => "cHandler_ModuleData",
                "/modulepackage/module/type" => "cHandler_ModuleData",
                "/modulepackage/module/output" => "cHandler_ModuleData",
                "/modulepackage/module/input" => "cHandler_ModuleData",
            ];

            // Add file handler (e.g. js, css, templates)
            foreach (array_keys($this->_packageStructure) As $sFileType) {
                // Note, that $aHandler["/modulepackage/" . $sFileType] and using
                // a handler which uses the node name (here: FileType) doesn't work,
                // as the event handler for the filetype node will be fired
                // after the node has been successfully parsed, not before.
                // So, we have a little redundancy here, but maybe we need
                // this in the future.
                $aHandler["/modulepackage/" . $sFileType . "/area"] = "cHandler_ItemArea";
                $aHandler["/modulepackage/" . $sFileType . "/name"] = "cHandler_ItemName";
                $aHandler["/modulepackage/" . $sFileType . "/content"] = "cHandler_ItemData";
            }

            // Layouts
            $aHandler["/modulepackage/layouts/area"] = "cHandler_ItemArea";
            $aHandler["/modulepackage/layouts/name"] = "cHandler_ItemName";
            $aHandler["/modulepackage/layouts/description"] = "cHandler_ItemData";
            $aHandler["/modulepackage/layouts/content"] = "cHandler_ItemData";

            // Translations
            $aHandler["/modulepackage/translations/language"] = "cHandler_ItemArea";
            $aHandler["/modulepackage/translations/string/original"] = "cHandler_ItemName";
            $aHandler["/modulepackage/translations/string/translation"] = "cHandler_Translation";

            $clXmlParser->setEventHandlers($aHandler);
        }

        if ($clXmlParser->parseFile($sFile)) {
            return true;
        } else {
            $this->_error = $clXmlParser->error;
            return false;
        }
    }

    /**
     * Imports the a module from a XML file
     * Uses xmlparser and callbacks
     *
     * @param string    $file     Filename of data file (full path)
     */
    public function import($sFile) {
        global $_mImport;

        if ($this->_parseImportFile($sFile, "module")) {
            $bStore = false;
            foreach ($_mImport["module"] as $key => $value) {
                if ($this->get($key) != $value) {
                    $this->set($key, addslashes($value));
                    $bStore = true;
                }
            }

            if ($bStore == true) {
                $this->store();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Exports the specified module strings to a file
     *
     * @param $filename string Filename to return
     * @param $return    boolean if false, the result is immediately sent to the browser
     */
    public function export($filename, $return = false) {
        $xmlTree = new XmlTree('1.0', 'ISO-8859-1');
        $root = & $xmlTree->addRoot('module');

        $root->appendChild("name", clHtmlSpecialChars($this->get("name")));
        $root->appendChild("description", clHtmlSpecialChars($this->get("description")));
        $root->appendChild("type", clHtmlSpecialChars($this->get("type")));
        $root->appendChild("input", clHtmlSpecialChars($this->get("input")));
        $root->appendChild("output", clHtmlSpecialChars($this->get("output")));

        if ($return == false) {
            ob_end_clean();
            header("Content-Type: text/xml");
            header("Etag: " . md5(random_int(0, mt_getrandmax())));
            header("Content-Disposition: attachment;filename=\"$filename\"");
            $xmlTree->dump(false);
        } else {
            return stripslashes($xmlTree->dump(true));
        }
    }

    public function getPackageOverview($sFile) {
        global $_mImport;

        if ($this->_parseImportFile($sFile, "package")) {
            $aData = [];
            $aData["guid"] = $_mImport["module"]["guid"];
            $aData["repository_guid"] = $_mImport["module"]["repository_guid"];
            $aData["name"] = $_mImport["module"]["name"];

            // Files
            foreach (array_keys($this->_packageStructure) as $sFileType) {
                if (is_array($_mImport["items"][$sFileType])) {
                    $aData[$sFileType] = array_keys($_mImport["items"][$sFileType]);
                }
            }

            // Layouts
            if (is_array($_mImport["items"]["layouts"])) {
                $aData["layouts"] = array_keys($_mImport["items"]["layouts"]);
            }

            // Translation languages
            if (is_array($_mImport["translations"])) {
                $aData["translations"] = array_keys($_mImport["translations"]);
            }

            return $aData;
        } else {
            return false;
        }
    }

    /**
     * Imports a module package from a XML file Uses xmlparser and callbacks
     *
     * @param string    $sFile         Filename of data file (including path)
     * @param array        $aOptions    Optional. An array of arrays specifying, how the items
     *                                 of the xml file will be imported. If specified, has to
     *                                 contain an array of this structure:
     *
     * $aOptions["items"][<filetype>][<clHtmlSpecialChars(filename)>]                = "skip", "append" or "overwrite";
     * $aOptions["translations"][<PackageLanguage>]    = <AssignedIDLang>;
     *
     *                                 If a file is not mentioned in the $aOptions["items"][<filetype>]
     *                                 array, it is new and will be imported.
     *
     *                                 If a <PackageLang> is not found in $aOptions["translations"],
     *                                 then the translations for this language will be ignored
     *
     * @return bool Returns true, if import has been successfully finished
     */
    public function importPackage($sFile, $aOptions = []) {
        $bStore = null;
        global $_mImport, $client;

        cInclude("includes", "functions.file.php");
        cInclude("includes", "functions.lay.php"); // You won't believe the code in there (or what is missing in class.layout.php...)
        // Ensure correct options structure
        foreach (array_keys($this->_packageStructure) as $sFileType) {
            if (!is_array($aOptions["items"][$sFileType])) {
                $aOptions["items"][$sFileType] = [];
            }
        }

        // Layouts
        if (!is_array($aOptions["items"]["layouts"])) {
            $aOptions["items"]["layouts"] = [];
        }

        // Translations
        if (!is_array($aOptions["translations"])) {
            $aOptions["translations"] = [];
        }

        // Parse file
        if ($this->_parseImportFile($sFile, "package")) {
            // Import data
            // Module
            foreach ($_mImport["module"] as $sKey => $sData) {
                if ($this->get($sKey) != $sData) {
                    $this->set($sKey, addslashes($sData));
                    $bStore = true;
                }
            }

            if ($bStore == true) {
                $this->store();
            }

            // Files
            foreach ($this->_packageStructure as $sFileType => $sFilePath) {
                if (is_array($_mImport["items"][$sFileType])) {
                    foreach ($_mImport["items"][$sFileType] as $sFileName => $aContent) {
                        if (!array_key_exists(clHtmlSpecialChars($sFileName), $aOptions["items"][$sFileType]) || $aOptions["items"][$sFileType][clHtmlSpecialChars($sFileName)] == "overwrite") {
                            if (!file_exists($sFilePath . $sFileName)) {
                                createFile($sFileName, $sFilePath);
                            }
                            fileEdit($sFileName, $aContent["content"], $sFilePath);
                        } elseif ($aOptions["items"][$sFileType][clHtmlSpecialChars($sFileName)] == "append") {
                            $sOriginalContent = getFileContent($sFileName, $sFilePath);
                            fileEdit($sFileName, $sOriginalContent . $aContent["content"], $sFilePath);
                        }
                    }
                }
            }

            // Layouts
            if (is_array($_mImport["items"]["layouts"])) {
                foreach ($_mImport["items"]["layouts"] as $sLayout => $aContent) {
                    if (!array_key_exists(clHtmlSpecialChars($sLayout), $aOptions["items"]["layouts"]) || $aOptions["items"]["layouts"][clHtmlSpecialChars($sLayout)] == "overwrite") {
                        $oLayouts = new cApiLayoutCollection;
                        $oLayouts->setWhere("idclient", $client);
                        $oLayouts->setWhere("name", $sLayout);
                        $oLayouts->query();

                        if (!$oLayout = $oLayouts->next()) {
                            layEditLayout(false, addslashes($sLayout), addslashes($aContent["description"]), addslashes($aContent["content"]));
                        } else {
                            layEditLayout($oLayout->get($oLayout->primaryKey), addslashes($sLayout), addslashes($aContent["description"]), addslashes($aContent["content"]));
                        }
                    } elseif ($aOptions["items"]["layouts"][clHtmlSpecialChars($sLayout)] == "append") {
                        $oLayouts = new cApiLayoutCollection;
                        $oLayouts->setWhere("idclient", $client);
                        $oLayouts->setWhere("name", $sLayout);
                        $oLayouts->query();

                        if (!$oLayout = $oLayouts->next()) {
                            layEditLayout(false, addslashes($sLayout), addslashes($aContent["description"]), addslashes($aContent["content"]));
                        } else {
                            layEditLayout($oLayout->get($oLayout->primaryKey), addslashes($sLayout), addslashes($oLayout->get("description") . $aContent["description"]), addslashes($oLayout->get("code") . $aContent["content"]));
                        }
                    }
                }
            }

            // Translations
            if (is_array($_mImport["translations"])) {
                $cApiModuleTranslationCollection = new cApiModuleTranslationCollection();
                $iID = $this->get($this->primaryKey);

                foreach (array_keys($_mImport["translations"]) as $sPackageLang) {
                    if (array_key_exists($sPackageLang, $aOptions["translations"])) {
                        foreach ($_mImport["translations"][$sPackageLang] as $sOriginal => $sTranslation) {
                            $cApiModuleTranslationCollection->create($iID, $aOptions["translations"][$sPackageLang], $sOriginal, $sTranslation);
                        }
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Exports the specified module and attached files to a file
     *
     * @param string    $sPackageFileName    Filename to return
     * @param bool        $bReturn            if false, the result is immediately sent to the browser
     */
    public function exportPackage($sPackageFileName, $bReturn = false) {
        global $cfgClient, $client;

        cInclude("includes", "functions.file.php");

        $xmlTree = new XmlTree('1.0', 'ISO-8859-1');
        $oRoot = & $xmlTree->addRoot('modulepackage');

        $oRoot->appendChild("package_guid", $this->get("package_guid"));
        $oRoot->appendChild("package_data", $this->get("package_data")); // This is serialized and more or less informal data

        $aData = unserialize($this->get("package_data"));
        if (!is_array($aData)) {
            $aData = [];
            $aData["repository_guid"] = "";
            $aData["jsfiles"] = [];
            $aData["tplfiles"] = [];
            $aData["cssfiles"] = [];
            $aData["layouts"] = [];
            $aData["translations"] = [];
        }

        // Export basic module
        $oNodeModule = & $oRoot->appendChild("module");
        $oNodeModule->appendChild("name", clHtmlSpecialChars($this->get("name")));
        $oNodeModule->appendChild("description", clHtmlSpecialChars($this->get("description")));
        $oNodeModule->appendChild("type", clHtmlSpecialChars($this->get("type")));
        $oNodeModule->appendChild("input", clHtmlSpecialChars($this->get("input")));
        $oNodeModule->appendChild("output", clHtmlSpecialChars($this->get("output")));

        // Export files (e.g. js, css, templates)
        foreach ($this->_packageStructure As $sFileType => $sFilePath) {
            $oNodeFiles = & $oRoot->appendChild($sFileType);
            foreach ($aData[$sFileType] as $sFileName) {
                if (is_readable($sFilePath . $sFileName)) {
                    $sContent = getFileContent($sFileName, $sFilePath);
                    $oNodeFiles->appendChild("area", clHtmlSpecialChars($sFileType));
                    $oNodeFiles->appendChild("name", clHtmlSpecialChars($sFileName));
                    $oNodeFiles->appendChild("content", clHtmlSpecialChars($sContent));
                }
            }
        }
        unset($sContent);

        // Export layouts
        $oNodeLayouts = & $oRoot->appendChild("layouts");

        $cApiLayoutCollection = new cApiLayoutCollection;
        $cApiLayoutCollection->setWhere("idclient", $client);
        $cApiLayoutCollection->query();

        while ($oLayout = $cApiLayoutCollection->next()) {
            if (in_array($oLayout->get($oLayout->primaryKey), $aData["layouts"])) {
                $oNodeLayouts->appendChild("area", "layouts");
                $oNodeLayouts->appendChild("name", clHtmlSpecialChars($oLayout->get("name")));
                $oNodeLayouts->appendChild("description", clHtmlSpecialChars($oLayout->get("description")));
                $oNodeLayouts->appendChild("content", clHtmlSpecialChars($oLayout->get("code")));
            }
        }
        unset($oLayout);
        unset($cApiLayoutCollection);

        // Export translations
        $cApiLanguageCollection = new cApiLanguageCollection();
        $cApiLanguageCollection->setOrder("idlang");
        $cApiLanguageCollection->query();

        if ($cApiLanguageCollection->count() > 0) {
            $iIDMod = $this->get($this->primaryKey);
            while ($oLang = $cApiLanguageCollection->next()) {
                $iID = $oLang->get($oLang->primaryKey);

                if (in_array($iID, $aData["translations"])) {
                    $oNodeTrans = & $oRoot->appendChild("translations");
                    // This is nice, but it doesn't help so much,
                    // as this data is available too late on import ...
                    $oNodeTrans->setNodeAttribs(["origin-language-id" => $iID, "origin-language-name" => clHtmlSpecialChars($oLang->get("name"))]);
                    // ... so we store the important information with the data
                    $oNodeTrans->appendChild("language", clHtmlSpecialChars($oLang->get("name")));

                    $oTranslations = new cApiModuleTranslationCollection;
                    $oTranslations->setWhere("idmod", $iIDMod);
                    $oTranslations->setWhere("idlang", $iID);
                    $oTranslations->query();

                    while ($oTranslation = $oTranslations->next()) {
                        $oNodeString = & $oNodeTrans->appendChild("string");
                        $oNodeString->appendChild("original", clHtmlSpecialChars($oTranslation->get("original")));
                        $oNodeString->appendChild("translation", clHtmlSpecialChars($oTranslation->get("translation")));
                    }
                }
            }
        }
        unset($cApiLanguageCollection);
        unset($oLang);

        if ($bReturn == false) {
            ob_end_clean();
            header("Content-Type: text/xml");
            header("Etag: " . md5(random_int(0, mt_getrandmax())));
            header("Content-Disposition: attachment;filename=\"$sPackageFileName\"");
            $xmlTree->dump(false);
        } else {
            return stripslashes($xmlTree->dump(true));
        }
    }

    /* dceModFileEdit (c)2009-2012 www.dceonline.de */

    /**
     * Overridden parent method for hooking dceModFileEdit
     * 
     * @return void
     */
    protected function _onLoad() {
        $this->_sModAlias = strtolower(uplCreateFriendlyName($this->get('name')));
        $this->_sModAliasOld = uplCreateFriendlyName($this->get('name'));
        $this->_setOutputFromPhpFile();
        $this->_setInputFromPhpFile();
    }

    /**
     * Use a PHP-file, if present, for module output
     * 
     * @return boolean
     */
    private function _setOutputFromPhpFile() {
        // dceModEdit not enabled or not present
        if ($this->_aModFileEditConf['use'] !== true) {
            return false;
        }
        return $this->_setFieldFromFile('output', $this->_sModAlias . "_output.php");
    }

    /**
     * Use a PHP-file, if present, for module input
     * 
     * @return boolean
     */
    private function _setInputFromPhpFile() {
        cInclude('includes', 'functions.upl.php');
        // dceModEdit not enabled or not present
        if ($this->_aModFileEditConf['use'] !== true) {
            return false;
        }
        return $this->_setFieldFromFile('input', $this->_sModAlias . "_input.php");
    }

    private function _displayNoteFromFile($bIsOldPath = FALSE) {
        if (property_exists($this, '_bNoted') && $this->_bNoted !== null && $this->_bNoted === true) {
            return;
        }
        global $frame, $area;
        if ($frame == 4 && $area == 'mod_edit') {
            $sAddMess = '';
            if ($bIsOldPath) {
                $sAddMess .= "<br>" . i18n("Using old CamelCase for name of modulefolder. You may lowercase the name for modulefolder");
            }
            $contenidoNotification = new Contenido_Notification();
            $contenidoNotification->displayNotification('warning', i18n("Module uses Output- and/or InputFromFile. Editing and Saving may not be possible in backend.") . $sAddMess);
            $this->_bNoted = true;
        }
    }

    /**
     * read file and set an object field
     *
     * @param string $sFile 
     * @param string $sField
     */
    private function _setFieldFromFile($sField, $sFile): bool {
        $bIsOldPath = TRUE;
        $sFile = strtolower($sFile);
        if (!str_contains($sFile, $this->_aModFileEditConf['modPath'])) {
            $sFile = $this->_aModFileEditConf['modPath'] . $sFile;
        }
        // check for new struct since CL 2.0
        if (is_dir($this->_aModFileEditConf['modPath'] . $this->_sModAlias) && is_writable($this->_aModFileEditConf['modPath'] . $this->_sModAlias) && file_exists($this->_aModFileEditConf['modPath'] .
                        $this->_sModAlias . "/php/" . $this->_sModAlias .
                        "_" . $sField . ".php")) {
            $sFile = $this->_aModFileEditConf['modPath'] .
                    $this->_sModAlias . "/php/" . $this->_sModAlias .
                    "_" . $sField . ".php";
            $bIsOldPath = FALSE;
        }

        if (is_file($sFile) && is_readable($sFile)) {
            $iFileSize = (int) filesize($sFile);
            if ($iFileSize > 0 && $fh = fopen($sFile, 'r')) {
                $this->set($sField, fread($fh, $iFileSize), false);
                fclose($fh);
                switch ($sField) {
                    case "output":
                        $this->_bOutputFromFile = true;
                        break;
                    case "input":
                        $this->_bInputFromFile = true;
                    default:
                        break;
                }
                $this->_displayNoteFromFile($bIsOldPath);
                return true;
            }
        } else {
            switch ($sField) {
                case "output":
                    $this->_bOutputFromFile = false;
                    break;
                case "input":
                    $this->_bInputFromFile = false;
                default:
                    break;
            }
        }
        return false;
    }

    public function isLoadedFromFile($sWhat = "all") {
        return match ($sWhat) {
            "all" => $this->_bOutputFromFile || $this->_bInputFromFile,
            "output" => $this->_bOutputFromFile,
            "input" => $this->_bInputFromFile,
            default => false,
        };
    }

    /* End dceModFileEdit (c)2009-2012 www.dceonline.de */

    private function _createModuleStruct() {
        $bDone = FALSE;
        if ($this->_aModFileEditConf['use'] == TRUE && is_writable($this->_sModPath)) {
            $bDone = TRUE;
            foreach ($this->_aModDefaultStruct as $sFolder) {
                $bDone = $bDone && mkdir($this->_sModPath . $sFolder);
            }
        }
        $this->_createModulePhpFiles();
        umask($this->_oldumask);
        return $bDone;
    }

    private function _createModulePhpFiles() {
        $sPath = $this->_sModPath . "php/";
        $aFileTpl = ['output' => "<?php\n\n?>", 'input' => "?><?php\n\n?><?php"];

        if (is_writable($sPath)) {
            $sOutputFile = $sPath . $this->_sModAlias . "_output.php";
            $sInputFile = $sPath . $this->_sModAlias . "_input.php";
            if (!file_exists($sOutputFile)) {
                $rFileHandler = fopen($sOutputFile, "w");
                if (is_resource($rFileHandler)) {
                    if ($this->getField('output') == "") {
                        fwrite($rFileHandler, $aFileTpl['output']);
                    } else {
                        fwrite($rFileHandler, $this->getField('output'));
                    }
                    fclose($rFileHandler);
                }
            }
            if (!file_exists($sInputFile)) {
                $rFileHandler = fopen($sInputFile, "w");
                if (is_resource($rFileHandler)) {
                    if ($this->getField('input') == "") {
                        fwrite($rFileHandler, $aFileTpl['input']);
                    } else {
                        fwrite($rFileHandler, $this->getField('input'));
                    }
                    fclose($rFileHandler);
                }
            }
        }
    }

}

// end class

class cApiModuleTranslationCollection extends ItemCollection {

    protected $_error;
    
    protected $f_obj;

    /**
     * Constructor Function
     * @param none
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["mod_translations"], "idmodtranslation");
        $this->_setItemClass("cApiModuleTranslation");
    }

    /**
     * Creates a new module translation item
     */
    public function create($idmod, $idlang, $original, $translation = false) {
        // Check if the original already exists. If it does,
        // update the translation if passed
        $cApiModuleTranslation = new cApiModuleTranslation();
        $sorg = $cApiModuleTranslation->_inFilter($original);

        $this->select("idmod = '$idmod' AND idlang = '$idlang' AND original = '$sorg'");

        if ($item = $this->next()) {
            if ($translation !== false) {
                $item->set("translation", $translation);
                $item->store();
            }
            return $item;
        } else {
            $item = parent::createNewItem();
            $item->set("idmod", $idmod);
            $item->set("idlang", $idlang);
            $item->set("original", $original);
            $item->set("translation", $translation);
            $item->store();
            return $item;
        }
    }

    /**
     * Fetches a translation
     *
     * @param $module int Module ID
     * @param $lang   int Language ID
     * @param $string string String to lookup
     */
    public function fetchTranslation($module, $lang, $string) {
        // If the f_obj does not exist, create one
        if (!is_object($this->f_obj)) {
            $this->f_obj = new cApiModuleTranslation();
        }

        // Create original string
        $sorg = $this->_itemClassInstance->_inFilter($string);

        // Look up
        $this->select("idmod = '$module' AND idlang='$lang' AND original = '$sorg'");

        if ($t = $this->next()) {
            $translation = $t->get("translation");

            if ($translation != "") {
                return $translation;
            } else {
                return $string;
            }
        } else {
            return $string;
        }
    }

    public function import($idmod, $idlang, $file) {
        global $_mImport;

        $clXmlParser = new clXmlParser("ISO-8859-1");

        $clXmlParser->setEventHandlers(["/module/translation/string/original" => "cHandler_ItemName", "/module/translation/string/translation" => "cHandler_Translation"]);

        $_mImport["current_item_area"] = "current"; // Pre-specification, as this won't be set from the XML file (here)

        if ($clXmlParser->parseFile($file)) {
            foreach ($_mImport["translations"]["current"] as $sOriginal => $sTranslation) {
                $this->create($idmod, $idlang, $sOriginal, $sTranslation);
            }

            return true;
        } else {
            $this->_error = $clXmlParser->error;
            return false;
        }
    }

    /**
     * Exports the specified module strings to a file
     *
     * @param $idmod    int Module ID
     * @param $idlang   int Language ID
     * @param $filename string Filename to return
     * @param $return    boolean if false, the result is immediately sent to the browser
     */
    public function export($idmod, $idlang, $filename, $return = false) {
        $cApiLanguage = new cApiLanguage($idlang);

        #$langstring = $langobj->get("name") . ' ('.$idlang.')';

        $cApiModuleTranslationCollection = new cApiModuleTranslationCollection;
        $cApiModuleTranslationCollection->select("idmod = '$idmod' AND idlang='$idlang'");

        $xmlTree = new XmlTree('1.0', 'ISO-8859-1');
        $root = & $xmlTree->addRoot('module');

        $translation = & $root->appendChild('translation');
        $translation->setNodeAttribs(["origin-language-id" => $idlang, "origin-language-name" => $cApiLanguage->get("name")]);

        while ($otranslation = $cApiModuleTranslationCollection->next()) {
            $string = &$translation->appendChild("string");

            $string->appendChild("original", clHtmlSpecialChars($otranslation->get("original")));
            $string->appendChild("translation", clHtmlSpecialChars($otranslation->get("translation")));
        }

        if ($return == false) {
            header("Content-Type: text/xml");
            header("Etag: " . md5(random_int(0, mt_getrandmax())));
            header("Content-Disposition: attachment;filename=\"$filename\"");
            $xmlTree->dump(false);
        } else {
            return $xmlTree->dump(true);
        }
    }

}

/**
 * Module access class
 */
class cApiModuleTranslation extends Item {

    /**
     * Constructor Function
     * @param $loaditem Item to load
     */
    public function __construct($loaditem = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["mod_translations"], "idmodtranslation");
        if ($loaditem !== false) {
            $this->loadByPrimaryKey($loaditem);
        }
    }

    public function useInFilter($sValue) {
        return $this->_inFilter($sValue);
    }

}

function cHandler_ModuleData($sName, $aAttribs, $sContent) {
    global $_mImport;
    $_mImport["module"][$sName] = $sContent;
}

// The following three functions references all file data (e.g. for css,
// js and template files) and layout data
// Note, that first the type is specified (from the "area" information
// in the xml file).
// Second, filename is specified based on "name" node content.
// Third, file content is stored using type, name and node content.
// You will have to specify individual handler functions, if one of
// the file areas may store additional data (e.g. a description)
function cHandler_ItemArea($sName, $aAttribs, $sContent) {
    global $_mImport;
    $_mImport["current_item_area"] = $sContent;
}

function cHandler_ItemName($sName, $aAttribs, $sContent) {
    global $_mImport;
    $_mImport["current_item_name"] = $sContent;
}

function cHandler_ItemData($sName, $aAttribs, $sContent) {
    global $_mImport;
    $_mImport["items"][$_mImport["current_item_area"]][$_mImport["current_item_name"]][$sName] = $sContent;
}

// Separate language area, as someone may specify "cssfiles" or something
// as language name, funny guy...
function cHandler_Translation($sName, $aAttribs, $sContent) {
    global $_mImport;
    $_mImport["translations"][$_mImport["current_item_area"]][$_mImport["current_item_name"]] = $sContent;
}
