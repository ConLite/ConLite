<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * logical cTree
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.12
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * {@internal 
 *   created 2004-08-04
 *   
 *   $Id$
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * class cTree
 * 
 */
class cTree extends cTreeItem {

    var $_treeIcon;

    function __construct($name = "") {
        /* The root item currently has to be a "0".
         * This is a bug, feel free to fix it. */
        cTreeItem::__construct(0, $name);
    }

    /**
     * sets a new name for the tree.
     *
     * @param string name Name of the tree
     * @return void
     * @access public
     */
    function setTreeName($name) {
        $this->setName($name);
    }

// end of member function setTreeName

    function setIcon($path) {
        $this->_treeIcon = $path;
    }

}
