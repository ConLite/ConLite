<?php
/**
 * File:
 * class.type.php
 *
 * Description:
 *  cApi class Type
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

class cApiTypeCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(cRegistry::getConfigValue('tab', 'type'), 'idtype');
        $this->_setItemClass("cApiType");
    }

}

class cApiType extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        parent::__construct(cRegistry::getConfigValue('tab', 'type'), 'idtype');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>