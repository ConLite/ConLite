<?php
/**
 * 
 * @package Core
 * @subpackage cApiClasses
 * 
 *   $Id: class.action.php 305 2014-03-05 22:41:23Z oldperl $:
 */
/**
 * based on
 * @package    Contenido Backend classes
 * @version    1.5
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 */

// security check
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cApiActionCollection extends ItemCollection {

    /**
     * 
     * @global array $cfg
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['actions'], 'idaction');
        $this->_setItemClass("cApiAction");
    }
    
    /**
     * 
     * @param string $area
     * @param string $name
     * @param string $code
     * @param string $location
     * @param int $relevant
     * @return cApiAction
     */
    public function create($area, $name, $code = "", $location = "", $relevant = 1) {
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
        $item->set("name", $name);
        $item->set("code", $code);
        $item->set("location", $location);
        $item->set("relevant", $relevant);

        $item->store();

        return ($item);
    }

}

class cApiAction extends Item {

    protected $_objectInvalid;

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        $this->_objectInvalid = false;

        parent::__construct($cfg['tab']['actions'], 'idaction');
        $this->setFilters(array("addslashes"), array("stripslashes"));

        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }

        // @todo  Where is this used???
        $this->_wantParameters = array();
    }
}
?>