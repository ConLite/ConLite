<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Article specification class
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    1.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created  unknown
 *   modified 2008-06-30, Dominik Ziegler, add security fix
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *
 *   $Id: class.artspec.php 2 2011-07-20 12:00:48Z oldperl $:
 * }}
 *
 */


if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class ArtSpecCollection extends ItemCollection
{
    /**
     * Constructor Function
     * @param none
     */
    public function __construct()
    {
        global $cfg;
        parent::__construct($cfg['tab']['art_spec'], "idartspec");
        $this->_setItemClass("ArtSpecItem");
    }
}


/**
 * Article specification Item
 */
class ArtSpecItem extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg['tab']['art_spec'], "idartspec");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}

?>