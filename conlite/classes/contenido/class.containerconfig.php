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
 * @version $Rev: 352 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.containerconfig.php 352 2015-09-24 12:12:51Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiContainerConfigurationCollection extends ItemCollection
{
    public function __construct($select = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["container_conf"], "idcontainerc");
        $this->_setItemClass("cApiContainerConfiguration");
        if ($select !== false) {
            $this->select($select);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiContainerConfigurationCollection($select = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($select = false);
    }

    public function create($idtplcfg, $number, $container)
    {
        $item = parent::create();
        $item->set("idtplcfg", $idtplcfg);
        $item->set("number", $number);
        $item->set("container", $container);
        $item->store();
    }
}


class cApiContainerConfiguration extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["container_conf"], "idcontainerc");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiContainerConfiguration($mId = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($mId);
    }
}

?>