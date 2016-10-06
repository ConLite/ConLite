<?php
/**
 * File:
 * class.clientslang.php
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
 * $Id: class.clientslang.php 352 2015-09-24 12:12:51Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiClientLanguageCollection extends ItemCollection
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $cfg;
        parent::__construct($cfg["tab"]["clients_lang"], "idclientslang");
        $this->_setItemClass("cApiClientLanguage");
    }
}


class cApiClientLanguage extends Item
{
    /**
     * Id of client
     * @var int
     */
    public $idclient;

    /**
     * Property collection instance
     * @var PropertyCollection
     */
    protected $_oPropertyCollection;

    /**
     * Constructor
     *
     * @param  int  $iIdClientsLang  If specified, load item
     * @param  int  $iIdClient       If idclient and idlang specified, load item; ignored, if idclientslang specified
     * @param  int  $iIdLang         If idclient and idlang specified, load item; ignored, if idclientslang specified
     */
    public function __construct($iIdClientsLang = false, $iIdClient = false, $iIdLang = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["clients_lang"], "idclientslang");

        if ($iIdClientsLang !== false) {
            $this->loadByPrimaryKey($iIdClientsLang);
        } elseif ($iIdClient !== false && $iIdLang !== false) {
            /*
            One way, but the other should be faster
            $oCollection = new cApiClientLanguageCollection;
            $oCollection->setWhere("idclient", $iIdClient);
            $oCollection->setWhere("idlang", $iIdLang);
            $oCollection->query();
            if ($oItem = $oCollection->next()) {
                $this->loadByPrimaryKey($oItem->get($oItem->primaryKey));
            }
            */

            // Query the database
            $sSQL = "SELECT %s FROM %s WHERE idclient = '%d' AND idlang = '%d'";
            $this->db->query($sSQL, $this->primaryKey, $this->table, $iIdClient, $iIdLang);
            if ($this->db->next_record()) {
                $this->loadByPrimaryKey($this->db->f($this->primaryKey));
            }
        }
    }

    /**
     * Load dataset by primary key
     *
     * @param   int  $iIdClientsLang
     * @return  bool
     */
    public function loadByPrimaryKey($iIdClientsLang)
    {
        if (parent::loadByPrimaryKey($iIdClientsLang) == true) {
            $this->idclient = $this->get("idclient");
            return true;
        }
        return false;
    }

    /**
     * Set client property
     *
     * @todo  Use parents method @see Item::setProperty()
     *
     * @param  mixed  $mType   Type of the data to store (arbitary data)
     * @param  mixed  $mName   Entry name
     * @param  mixed  $mValue  Value
     */
    public function setProperty($mType, $mName, $mValue)
    {
        $oPropertyColl = $this->_getPropertiesCollectionInstance();
        $oPropertyColl->setValue($this->primaryKey, $this->get($this->primaryKey), $mType, $mName, $mValue);
    }

    /**
     * Get client property
     *
     * @todo  Use parents method @see Item::getProperty()
     *
     * @param   mixed  $mType   Type of the data to get
     * @param   mixed  $mName   Entry name
     * @return  mixed  Value
     */
    public function getProperty($mType, $mName)
    {
        $oPropertyColl = $this->_getPropertiesCollectionInstance();
        return $oPropertyColl->getValue($this->primaryKey, $this->get($this->primaryKey), $mType, $mName);
    }

    /**
     * Delete client property
     *
     * @todo  Use parents method @see Item::deleteProperty(), but be carefull, different parameter!
     *
     * @param   int  $idprop   Id of property
     * @return  void
     */
    public function deletePropertyById($idprop)
    {
        $oPropertyColl = $this->_getPropertiesCollectionInstance();
        $oPropertyColl->delete($idprop);
    }

    /**
     * Get client properties by type
     *
     * @param   mixed  $mType   Type of the data to get
     * @return  array  Assoziative array
     */
    public function getPropertiesByType($mType)
    {
        $oPropertyColl = $this->_getPropertiesCollectionInstance();
        return $oPropertyColl->getValuesByType($this->primaryKey, $this->idclient, $mType);
    }

    /**
     * Get all client properties
     *
     * @param   mixed  $mType   Type of the data to get
     * @return  array|false  Assoziative array
     * @todo    return value should be the same as getPropertiesByType(), e. g. an empty array instead false
     */
    public function getProperties()
    {
        $itemtype = Contenido_Security::escapeDB($this->primaryKey, $this->db);
        $itemid   = Contenido_Security::escapeDB($this->get($this->primaryKey), $this->db);
        $oPropertyColl = $this->_getPropertiesCollectionInstance();
        $oPropertyColl->select("itemtype='".$itemtype."' AND itemid='".$itemid."'", "", "type, value ASC");

        if ($oPropertyColl->count() > 0) {
            $aArray = array();

            while ($oItem = $oPropertyColl->next()) {
                $aArray[$oItem->get('idproperty')]['type']  = $oItem->get('type');
                $aArray[$oItem->get('idproperty')]['name']  = $oItem->get('name');
                $aArray[$oItem->get('idproperty')]['value'] = $oItem->get('value');
            }

            return $aArray;
        } else {
            return false;
        }
    }

    /**
     * Lazy instantiation and return of properties object
     *
     * @return PropertyCollection
     */
    protected function _getPropertiesCollectionInstance()
    {
        // Runtime on-demand allocation of the properties object
        if (!is_object($this->_oPropertyCollection)) {
            $this->_oPropertyCollection = new PropertyCollection();
            $this->_oPropertyCollection->changeClient($this->idclient);
        }
        return $this->_oPropertyCollection;
    }
}

?>