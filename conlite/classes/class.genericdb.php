<?php

/**
 * @package    Contenido Backend classes
 * @version    $Id$
 * @author     Timo A. Hummel
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

global $cfg;

// Try to load GenericDB database driver
$driver_filename = cRegistry::getBackendPath()
    . cRegistry::getConfigValue('path', 'classes')
    . 'drivers'
    . DIRECTORY_SEPARATOR
    . cRegistry::getConfigValue('sql', 'gdb_driver')
        . DIRECTORY_SEPARATOR
    . 'class.gdb.'
    . cRegistry::getConfigValue('sql', 'gdb_driver')
    . '.php';

if (file_exists($driver_filename)) {
    include_once($driver_filename);
}

/**
 * Class Contenido_ItemException.
 * @author     Murat Purc <murat@purc.de>
 * @version    0.1
 * @copyright  four for business AG <www.4fb.de>
 */
class Contenido_ItemException extends Exception {
    
}

/**
 * Class ItemCollection
 * Abstract class for database based item collections.
 *
 * @author     Timo A. Hummel <Timo.Hummel@4fb.de>
 * @author     Murat Purc <murat@purc.de>
 * @version    0.2
 * @copyright  four for business 2003
 */
abstract class ItemCollection extends \ConLite\GenericDb\ItemCollection {

}

/**
 * Class Item
 * Abstract class for database based items.
 *
 * @author     Timo A. Hummel <Timo.Hummel@4fb.de>
 * @author     Murat Purc <murat@purc.de>
 * @version    0.3
 * @copyright  four for business 2003
 */
abstract class Item extends \ConLite\GenericDb\Item {

}
