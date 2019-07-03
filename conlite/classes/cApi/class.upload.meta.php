<?php
/**
 * 
 * @package Core
 * @subpackage cApiClasses
 * @version $Rev$
 * @author Ortwin Pinke
 * @copyright (c) 2015, CL-Team
 * 
 *   $Id$:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiUploadMetaCollection extends ItemCollection {

    /**
     * Constructor Function
     * @param none
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["upl_meta"], "id_uplmeta");
        $this->_setItemClass("cApiUploadMeta");

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiUploadCollection');
    }

    public function create($idupl, $idlang, $medianame = '', $description = '', $keywords = '', $internal_notice = '', $copyright = '', $author = '', $created = '', $modified = '', $modifiedby = '') {
        global $auth;

        if (empty($author)) {
            $author = $auth->auth['uname'];
        }
        if (empty($created)) {
            $created = date('Y-m-d H:i:s');
        }
        if (empty($modified)) {
            $modified = date('Y-m-d H:i:s');
        }

        $oItem = $this->createNewItem();

        $oItem->set('idupl', $idupl);
        $oItem->set('idlang', $idlang);
        $oItem->set('medianame', $medianame);
        $oItem->set('description', $description);
        $oItem->set('keywords', $keywords);
        $oItem->set('internal_notice', $internal_notice);
        $oItem->set('author', $author);
        $oItem->set('created', $created);
        $oItem->set('modified', $modified);
        $oItem->set('modifiedby', $modifiedby);
        $oItem->set('copyright', $copyright);
        $oItem->store();

        return $oItem;
    }
}

class cApiUploadMeta extends Item {

    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["upl_meta"], "id_uplmeta");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
    
    public function loadByUploadIdAndLanguageId($idupl, $idlang) {
        $aProps = array(
            'idupl' => $idupl,
            'idlang' => $idlang
        );
        $aRecordSet = $this->_oCache->getItemByProperties($aProps);
        if ($aRecordSet) {
            // entry in cache found, load entry from cache
            $this->loadByRecordSet($aRecordSet);
            return true;
        } else {
            $where = $this->db->prepare('idupl = %d AND idlang = %d', $idupl, $idlang);
            return $this->_loadByWhereClause($where);
        }
    }
    
    public function setField($name, $value, $bSafe = true) {
        switch ($name) {
            case 'idupl':
            case 'idlang':
                $value = (int) $value;
                break;
        }

        parent::setField($name, $value, $bSafe);
    }
}
?>