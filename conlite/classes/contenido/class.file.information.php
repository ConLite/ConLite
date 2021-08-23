<?php

/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiFileInformationCollection extends ItemCollection {

    public function __construct() {
        parent::__construct(cRegistry::getConfigValue('tab', 'file_information'), 'idsfi');
        $this->_setItemClass("cApiFileInformation");
    }
    
    public function create($sFilename, $sType, $sDescription = '') {
        $iClient = cRegistry::getClientId();
        $oAuth = cRegistry::getAuth();
        
        $oItem = new cApiFileInformation();
        $oItem->loadByMany(array(
            'idclient' => $iClient,
            'type' => $sType,
            'filename' => $sFilename
        ));
        
         if (!$oItem->isLoaded()) {
            $oItem = $this->createNewItem();

            $oItem->set('idclient', $iClient);
            $oItem->set('type', $sType);
            $oItem->set('filename', $sFilename);
            $oItem->set('created', date('Y-m-d H:i:s'));
            $oItem->set('lastmodified', date('Y-m-d H:i:s'));
            $oItem->set('author', $oAuth->auth['uid']);
            $oItem->set('modifiedby', $oAuth->auth['uid']);
            $oItem->set('description', $sDescription);
            $oItem->store();

            return $oItem;
        } else {
            return $this->update($sFilename, $sType, $sDescription);
        }
    }
    
    public function update($sFilename, $sType, $sDescription = '', $sNewFilename = '', $sAuthor = '') {
        $oAuth = cRegistry::getAuth();
        $iClient = cRegistry::getClientId();
        $oItem = new cApiFileInformation();
        $oItem->loadByMany(array(
            'idclient' => $iClient,
            'type' => $sType,
            'filename' => $sFilename
        ));
        $iId = $oItem->get('idsfi');
        if ($oItem->isLoaded()) {
            $oItem->set('idsfi', $iId);
            $oItem->set('lastmodified', date('Y-m-d H:i:s'));
            $oItem->set('description', $sDescription);
            $oItem->set('modifiedby', $oAuth->auth['uid']);
            if (!empty($sNewFilename)) {
                $oItem->set('filename', $sNewFilename);
            }
            if (!empty($sAuthor)) {
                $oItem->set('author', $sAuthor);
            }
            $oItem->store();
        }

        return $oItem;
    }

}

class cApiFileInformation extends Item {

    public function __construct($mId = false) {
        parent::__construct(cRegistry::getConfigValue('tab', 'file_information'), 'idsfi');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}
