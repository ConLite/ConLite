<?php
/**
 * File:
 * class.cHTML.Meta.php
 *
 * Description:
 *  cHTML Meta
 * 
 * @package Core
 * @subpackage cHTML
 * @version $Rev: 369 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.chtml5.meta.php 369 2015-10-27 10:53:15Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Creates an ordered or unordered List
 * 
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cHTML5Meta extends cHTML {
    
    /**
     * Constructor
     * 
     * @param boolean $bUnorderd use ul or ol default: TRUE = ul
     */
    public function __construct() {        
        parent::__construct();
        $this->_tag = "meta";
    }
    
    public function toHTML() {
        return parent::toHTML();
    }
}