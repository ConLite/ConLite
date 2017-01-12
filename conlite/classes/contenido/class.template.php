<?php

/**
 * File:
 * class.template.php
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
 * $Id: class.template.php 352 2015-09-24 12:12:51Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiTemplateCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["tpl"], "idtpl");
        $this->_setItemClass("cApiTemplate");
        if ($select !== false) {
            $this->select($select);
        }
    }

    public function setDefaultTemplate($idtpl) {
        global $cfg, $client;

        $db = new DB_ConLite();
        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET defaulttemplate = 0 WHERE idclient = '" . Contenido_Security::toInteger($client) . "'";
        $db->query($sql);

        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET defaulttemplate = 1 WHERE idtpl = '" . Contenido_Security::toInteger($idtpl) . "'";
        $db->query($sql);
    }

}

class cApiTemplate extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["tpl"], "idtpl");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}

?>