<?php

/**
 * File:
 * class.category.php
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
 * $Id: class.category.php 352 2015-09-24 12:12:51Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiCategoryCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["cat"], "idcat");
        $this->_setItemClass("cApiCategory");
        if ($select !== false) {
            $this->select($select);
        }
    }

}

class cApiCategory extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["cat"], "idcat");
        $this->setFilters(array(), array());

        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}

?>