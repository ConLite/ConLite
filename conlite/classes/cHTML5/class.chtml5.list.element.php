<?php

/**
 * File:
 * class.cHTML.List.php
 *
 * Description:
 *  cHTML List Element
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
 * $Id: class.chtml5.list.element.php 369 2015-10-27 10:53:15Z oldperl $
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Creates a listelement to use with cHTMLList
 * 
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @see cHTMLList
 */
class cHTML5ListElement extends cHTML {

    /**
     * Constructor
     * 
     * @param boolean $bUnorderd use ul or ol default: TRUE = ul
     */
    public function __construct($mContent = "") {
        parent::__construct();

        if (is_object($mContent) && method_exists($mContent, "render")) {
            $mContent = $mContent->render();
        }
        $this->setContent($mContent);
        $this->setContentlessTag(false);
        $this->_tag = "li";
    }

    public function setContent($content) {
        $this->_setContent($content);
    }

    public function toHTML() {
        return parent::toHTML();
    }

}
