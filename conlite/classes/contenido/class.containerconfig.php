<?php

/**
 * File:
 * class.containerconfig.php
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

class cApiContainerConfigurationCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["container_conf"], "idcontainerc");
        $this->_setItemClass("cApiContainerConfiguration");
        if ($select !== false) {
            $this->select($select);
        }
    }

    public function create($idtplcfg, $number, $container) {
        $item = parent::createNewItem();
        $item->set("idtplcfg", $idtplcfg);
        $item->set("number", $number);
        $item->set("container", $container);
        $item->store();
    }

}

class cApiContainerConfiguration extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["container_conf"], "idcontainerc");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>