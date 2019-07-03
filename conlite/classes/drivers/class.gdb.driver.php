<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * @package    Contenido Backend classes
 * @version    1.3
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * $Id$
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class gdbDriver {

    var $_sEncoding;
    var $_oItemClassInstance;

    public function __construct() {
        
    }

    public function setEncoding($sEncoding) {
        $this->_sEncoding = $sEncoding;
    }

    public function setItemClassInstance($oInstance) {
        $this->_oItemClassInstance = $oInstance;
    }

    public function buildJoinQuery($destinationTable, $destinationClass, $destinationPrimaryKey, $sourceClass, $primaryKey) {
        
    }

    public function buildOperator($sField, $sOperator, $sRestriction) {
        
    }
}
?>
