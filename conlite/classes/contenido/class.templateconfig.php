<?php
/**
 * File:
 * class.templateconfig.php
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
 * $Id: class.templateconfig.php 352 2015-09-24 12:12:51Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiTemplateConfigurationCollection extends ItemCollection
{
    public function __construct($select = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["tpl_conf"], "idtplcfg");
        $this->_setItemClass("cApiTemplateConfiguration");
        if ($select !== false) {
            $this->select($select);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function FrontendPermission($select = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($select);
    }

    public function delete($idtplcfg)
    {
        $result = parent::delete($idtplcfg);
        $oContainerConfCollection = new cApiContainerConfigurationCollection ("idtplcfg = '$idtplcfg'");
        $aDelContainerConfIds = array();
        while ($oContainerConf = $oContainerConfCollection->next()) {
            array_push($aDelContainerConfIds, $oContainerConf->get('idcontainerc'));
        }

        foreach($aDelContainerConfIds as $iDelContainerConfId) {
            $oContainerConfCollection->delete($iDelContainerConfId);
        }
    }

    public function create($idtpl)
    {
        global $auth;

        $item = parent::createNewItem();
        $item->set("idtpl", $idtpl);
        $item->set("author", $auth->auth['uname']);
        $item->set("status", 0);
        $item->set("created", date('YmdHis'));
        $item->set("lastmodified", '0000-00-00 00:00:00');
        $item->store();

        $iNewTplCfgId = $item->get("idtplcfg");

        #if there is a preconfiguration of template, copy its settings into templateconfiguration
        $templateCollection = new cApiTemplateCollection("idtpl = '$idtpl'");

        if ($template = $templateCollection->next()) {
            $idTplcfgStandard = $template->get("idtplcfg");
            if ($idTplcfgStandard > 0) {
                $oContainerConfCollection = new cApiContainerConfigurationCollection ("idtplcfg = '$idTplcfgStandard'");
                $aStandardconfig = array();
                while ($oContainerConf = $oContainerConfCollection->next()) {
                    $aStandardconfig[$oContainerConf->get('number')] = $oContainerConf->get('container');
                }

                foreach ($aStandardconfig as $iContainernumber => $sContainer) {
                    $oContainerConfCollection->create($iNewTplCfgId, $iContainernumber, $sContainer);
                }
            }
        }

        return ($item);
    }
}


class cApiTemplateConfiguration extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["tpl_conf"], "idtplcfg");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiTemplateConfiguration($mId = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($mId = false);
    }
}

?>