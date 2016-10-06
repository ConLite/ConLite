<?php
/**
 * File:
 * class.chtml.button.php
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
 * $Id: class.chtml5.button.php 369 2015-10-27 10:53:15Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Creates an ordered or unordered List
 * 
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cHTML5Button extends cHTMLFormElement {
    
    
    public function __construct($name, $id = "", $disabled = "", $tabindex = "", $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->setContentlessTag(FALSE);
        $this->_tag = "button";
    }


    public function setContent($mContent='') {
        if(is_object($mContent) && method_exists($mContent, "render")) {
            $mContent = $mContent->render();
        }
        $this->_setContent($mContent);
    }

    public function toHTML() {
        return parent::toHTML();
    }
}