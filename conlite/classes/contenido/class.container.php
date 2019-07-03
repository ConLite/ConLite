<?php

/**
 * File:
 * class.container.php
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
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiContainerCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["container"], "idcontainer");
        $this->_setItemClass("cApiContainer");
        if ($select !== false) {
            $this->select($select);
        }
    }

    public function clearAssignments($idtpl) {
        $this->select("idtpl = '$idtpl'");
        while ($item = $this->next()) {
            $this->delete($item->get("idcontainer"));
        }
    }

    public function assignModul($idtpl, $number, $module) {
        $this->select("idtpl = '$idtpl' AND number = '$number'");
        if ($item = $this->next()) {
            $item->set("module", $module);
            $item->store();
        } else {
            $this->create($idtpl, $number, $module);
        }
    }

    public function create($idtpl, $number, $module) {
        $item = parent::createNewItem();
        $item->set("idtpl", $idtpl);
        $item->set("number", $number);
        $item->set("module", $module);
        $item->store();
    }

}

class cApiContainer extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["container"], "idcontainer");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}

?>