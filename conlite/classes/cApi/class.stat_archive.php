<?php
/**
 * File:
 * class.stat.php
 *
 * Description:
 *  
 * 
 * @package Core
 * @subpackage cApiClasses
 * @version $Rev$
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2016, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');


class cApiStatCollection extends ItemCollection {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        $this->_setItemClass("cApiStat");
    }
}

class cApiStat extends Item {
    
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>