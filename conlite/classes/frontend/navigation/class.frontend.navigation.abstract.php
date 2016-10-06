<?php
/**
 * File:
 * class.frontend.navigation.abstract.php
 *
 * Description:
 *  Abstract Class for Frontend Navigations
 * 
 * @package Core
 * @subpackage Frontend
 * @version $Rev:$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2016, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id:$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class cFrontendNavigationAbstract {
    
    /**
     *
     * @var DB_ConLite
     */
    protected $_oDB;
    
    public function __construct() {
        ;
    }
    
    protected function _getDB() {
        
    }
}
