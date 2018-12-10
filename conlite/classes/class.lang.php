<?php

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Class for language management and information
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    1.1
 * @deprecated since ConLite version 2.0.0 will be removed in future, use cApiLanguage instead
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created  2003-05-20
 *   modified 2008-06-30, Dominik Ziegler, add security fix
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *
 *   $Id: class.lang.php 123 2012-08-30 11:11:09Z oldperl $:
 * }}
 *
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Class Language
 * Class for language collections
 * @author Timo A. Hummel <Timo.Hummel@4fb.de>
 * @deprecated since version 2.0.0 use cApiLanguageCollection instead
 * @version 0.1
 * @copyright four for business 2003
 */
class Languages extends cApiLanguageCollection {

    /**
     * Constructor
     * @param none
     */
    public function __construct() {
        parent::__construct();
    }

}

/**
 * Class Language
 * Class for a single language item
 * @author Timo A. Hummel <Timo.Hummel@4fb.de>
 * @version 0.1
 * @deprecated since CL version 2.0.0 use cApiLanguage instead
 * @copyright four for business 2003
 */
class Language extends cApiLanguage {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        if ($mId === false) {
            parent::__construct();
        } else {
            parent::__construct($mId);
        }
    }

}
