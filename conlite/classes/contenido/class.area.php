<?php

/**
 * File:
 * class.area.php
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
 * $Id: class.area.php 352 2015-09-24 12:12:51Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiAreaCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['area'], 'idarea');
        $this->_setItemClass("cApiArea");
    }

}

class cApiArea extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['area'], 'idarea');
        $this->setFilters(array("addslashes"), array("stripslashes"));
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    public function create($name, $parentid = 0, $relevant = 1, $online = 1) {
        $item = parent::createNewItem();

        $item->set("name", $name);
        $item->set("relevant", $relevant);
        $item->set("online", $online);
        $item->set("parent_id", $parentid);

        $item->store();

        return ($item);
    }

    public function createAction($area, $name, $code, $location, $relevant) {
        $ac = new cApiActionCollection();
        $a = $ac->create($area, $name, $code, $location, $relevant);
    }

}

?>