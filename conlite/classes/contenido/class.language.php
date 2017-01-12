<?php

/**
 * File:
 * class.language.php
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
 * $Id: class.language.php 352 2015-09-24 12:12:51Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiLanguageCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["lang"], "idlang");
        $this->_setItemClass("cApiLanguage");
        $this->_setJoinPartner("cApiClientLanguageCollection");
    }

    public function nextAccessible() {
        global $perm, $client, $cfg, $lang;

        $item = parent::next();

        $db = new DB_ConLite();
        $lang = Contenido_Security::toInteger($lang);
        $client = Contenido_Security::toInteger($client);

        $sql = "SELECT idclient FROM " . $cfg["tab"]["clients_lang"] . " WHERE idlang = '" . $lang . "'";
        $db->query($sql);

        if ($db->next_record()) {
            if ($client != $db->f("idclient")) {
                $item = $this->nextAccessible();
            }
        }

        if ($item) {
            if ($perm->have_perm_client("lang[" . $item->get("idlang") . "]") ||
                    $perm->have_perm_client("admin[" . $client . "]") ||
                    $perm->have_perm_client()) {
                // Do nothing for now
            } else {
                $item = $this->nextAccessible();
            }

            return $item;
        } else {
            return false;
        }
    }

    /**
     * Returns an array with language ids or an array of values if $bWithValues is true
     * 
     * @param int $iClient
     * @param boolean $bWithValues
     * @return array
     */
    public function getClientLanguages($iClient, $bWithValues = false) {
        $aList = array();
        $oClientLangCol = new cApiClientLanguageCollection();
        $oClientLangCol->setWhere("idclient", $iClient);
        $oClientLangCol->query();

        while ($oItem = $oClientLangCol->next()) {
            $mTmpValues = '';
            if ($bWithValues) {
                $oLanguage = new cApiLanguage($oItem->get("idlang"));
                $mTmpValues = array(
                    "idlang" => $oItem->get("idlang"),
                    "name" => $oLanguage->get("name"),
                    "active" => ($oLanguage->get("active")) ? true : false,
                    "encoding" => $oLanguage->get("encoding")
                );
                unset($oLanguage);
            } else {
                $mTmpValues = $oItem->get("idlang");
            }
            $aList[$oItem->get("idlang")] = $mTmpValues;
        }
        unset($oClientLangCol, $oItem);
        return $aList;
    }

}

class cApiLanguage extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["lang"], "idlang");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}

?>