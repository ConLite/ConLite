<?php

/**
 * File:
 * class.framfile.php
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

class cApiFrameFileCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['framefiles'], 'idframefile');
        $this->_setItemClass("cApiFrameFile");
    }

    public function create($area, $idframe, $idfile) {
        $item = parent::createNewItem();

        if (is_string($area)) {
            $c = new cApiArea();
            $c->loadBy("name", $area);

            if ($c->virgin) {
                $area = 0;
                cWarning(__FILE__, __LINE__, "Could not resolve area [$area] passed to method [create], assuming 0");
            } else {
                $area = $c->get("idarea");
            }
        }

        $item->set("idarea", $area);
        $item->set("idfile", $idfile);
        $item->set("idframe", $idframe);

        $item->store();

        return ($item);
    }

}

class cApiFrameFile extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["framefiles"], "idframefile");
        $this->setFilters(array("addslashes"), array("stripslashes"));
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}
?>